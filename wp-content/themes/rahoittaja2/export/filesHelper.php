<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$base_template_dir = get_template_directory();
$base_upload_dir = wp_upload_dir();
load_template($base_template_dir . '/export/ProviderLog.php');

class FilesHelper {

    public static function find_files($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        if (!is_array($files)) {
            $files = array();
        }
        $folders = glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT);
        if (!is_array($folders)) {
            $folders = array();
        }
        foreach ($folders as $dir) {
            $files = array_merge($files, FilesHelper::find_files($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }

    public static function generate_fso($path, $recursive = true) {
        if (is_file($path)) {
            $content = file_get_contents($path);

            if ($content === false)
                throw new PermissionDeniedException($path);

            $type = 'text';
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            if (in_array($ext, array('jpg', 'jpeg', 'bmp', 'png', 'gif', 'svg'))) {
                $type = 'data';
                $content = base64_encode($content);
            }

            return array(
                'type' => $type,
                'content' => $content
            );
        }

        if (!is_dir($path))
            return array();

        $result = array(
            'type' => 'dir',
            'items' => array()
        );

        if ($handle = opendir($path)) {
            while (($name = readdir($handle)) !== false) {
                if (preg_match('#^\.#', $name))
                    continue;

                $result['items'][$name] = FilesHelper::generate_fso($path . "/" . $name, $recursive);
            }
            closedir($handle);
        }
        return $result;
    }

    public static function enumerate_files($path, $recursive = true) {
        $files = array();
        if (!is_dir($path)) {
            return $files;
        }

        if ($handle = opendir($path)) {
            while (($name = readdir($handle)) !== false) {
                if (preg_match('#^\.#', $name)) {
                    continue;
                }

                if (is_dir($path . "/" . $name) && $recursive) {
                    $files = array_merge($files, FilesHelper::enumerate_files($path . "/" . $name, $recursive));
                } else {
                    $files[] = array('path' => $path . '/' . $name);
                }
            }
            closedir($handle);
        }

        return $files;
    }

    public static function copy_recursive($source, $destination, $change_file = null) {
        if(is_file($source)) {
            if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0777, true)) {
                return;
            }
            if($change_file && preg_match('#^(?!.*[\\/]{1}(export|fonts|images|languages|library)[\\/]{1}.*).*\.php$#', $source) > 0) {
                $content = call_user_func($change_file, $source);
                if (false === file_put_contents($destination, $content))
                    throw new PermissionDeniedException($destination);
            } else {
                copy($source, $destination);
            }
        } elseif(is_dir($source)) {
            if(!is_dir($destination)) {
                if(!mkdir($destination)) {
                    return;
                }
            }
            if ($dh = opendir($source)) {
                while (($file = readdir($dh)) !== false) {
                    if('.' == $file || '..' == $file) {
                        continue;
                    }
                    self::copy_recursive($source . '/' . $file, $destination . '/' . $file, $change_file);
                }
                closedir($dh);
            }
        }
    }

    public static function readfile($path) {
        if (false === @readfile($path))
            throw new PermissionDeniedException($path);
    }

    public static function write($path, $content) {
        if (false === file_put_contents($path, $content))
            throw new PermissionDeniedException($path);
    }

    public static function rename($from, $to) {
        if (false === rename($from, $to))
            throw new PermissionDeniedException($from);
    }

    public static function rename_if_exists($from, $to) {
        if (file_exists($from))
            FilesHelper::rename($from, $to);
    }

    public static function remove_file($path) {
        if (is_file($path) && false === @unlink($path))
            throw new PermissionDeniedException($path);
    }

    public static function create_dir($dir) {
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true))
            throw new PermissionDeniedException($dir);
    }

    /**
     * Remove directory recursively
     *
     * @param $dir
     * @param bool|false $hard - remove directory itself
     * @throws PermissionDeniedException
     */
    public static function empty_dir($dir, $hard = false) {
        if (!file_exists($dir) || !is_dir($dir))
            return;
        if (!is_readable($dir))
            return;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                $path = $dir . '/' . $object;
                if (strtolower(filetype($path)) == 'dir') {
                    FilesHelper::empty_dir($path, true);
                } else if (false === @unlink($path)) {
                    throw new PermissionDeniedException("Can't unlink $path");
                }
            }
        }
        reset($objects);
        if ($hard && false === @rmdir($dir)) {
            ProviderLog::errorHandler(E_USER_ERROR, "Can't rmdir $dir" . (isset($php_errormsg) ? ", $php_errormsg" : ""), __FILE__, __LINE__);
        }
    }

    /**
     * Remove empty subdirectories recursively in provided path
     *
     * @param $path
     * @return bool
     */
    public static function remove_empty_subfolders($path) {
        $empty = true;
        $files = glob($path . DIRECTORY_SEPARATOR . "*");
        foreach ($files as $file) {
            $empty &= is_dir($file) && FilesHelper::remove_empty_subfolders($file);
        }

        if ($empty && false === @rmdir($path)) {
            ProviderLog::errorHandler(E_USER_ERROR, "Can't rmdir $path" . (isset($php_errormsg) ? ", $php_errormsg" : ""), __FILE__, __LINE__);
        }
        return $empty;
    }

    /**
     * Normalize given path
     *
     * Example:
     * /home/a/../b/c
     * /home/b/c
     *
     * @param $path
     * @return string
     */
    public static function normalize_path($path) {
        $root = $path[0] === '/' ? '/' : '';
        $segments = preg_split('/[\\/\\\\]/', trim($path, '/'));
        $ret = array();
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '') {
                continue;
            }
            if ($segment === '..') {
                array_pop($ret);
            } else {
                array_push($ret, $segment);
            }
        }
        return $root . implode('/', $ret);
    }

    /**
     * Check if provided directory readable and writable
     *
     * @param $path
     * @return bool
     * @throws PermissionDeniedException
     */
    public static function test_permission($path) {
        if (is_file($path)) {
            $path = dirname($path);
        }

        if (!is_writable($path)) {
            throw new PermissionDeniedException('You do not have permission to write to this directory: ' . $path);
        }

        if (!is_readable($path)) {
            throw new PermissionDeniedException('You do not have permission to read from this directory: ' . $path);
        }
        return true;
    }
}

class PermissionDeniedException extends Exception {

    public function getExtendedMessage() {
        $msg = parent::getMessage();
        $last_error = error_get_last();
        if ($last_error) {
            $last_error_desc = '<p>Last PHP error: <span style="color:#8a6d3b">' . $last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'] . '</span></p>';
        } else {
            $last_error_desc = '';
        }

        $base_upload_dir = wp_upload_dir();

        if (!empty($base_upload_dir['basedir']) && strpos($msg, $base_upload_dir['basedir']) !== false) {
            $uploads_waring = ' <br><strong>Please check correctness of upload path in <i>Settings &raquo; Media &raquo; Uploading Files</i></strong>';
        } else {
            $uploads_waring = '';
        }

        return '<h2>Insufficient permissions.</h2><p>' . $msg . $uploads_waring . $last_error_desc . '</p><p>'
            . 'The theme cannot be edited. Please make sure that the user and group running web server is granted the appropriate read, write and execute(linux only) permissions on the following folders. As well as read and write permission on the files in these folders:</p>'
            . '{folders}'
            . '<p>How to do this for MacOS and Linux systems:</p>'
            . '<ol>'
            .   '<li>login ssh/terminal under privileged user, get sufficient access rights if need using <b>sudo</b> or <b>su</b> to make next changes</li>'
            .   '<li>cd ' . ABSPATH . '</li>'
            .   '<li>chmod -R u=rwX,g=rX folder_name'
            .      '<br><i>For example: chmod -R u=rwX,g=rX app/code/local</i>'
            .   '</li>'
            .   '<li>chown -R &#60;user>:&#60;group> folder_name'
            .      '<br><i>For example: chown -R apache:apache app/code/local</i>'
            .   '</li>'
            . '</ol>'
            . '<p><b>Note</b>: It is general approach. We would recommend that you ask your hosting administrator to grant access permissions for listed folders and files.</p><br>'
            . '<!--' . $this->getTraceAsString() . '-->';
    }
}

class UnzipException extends Exception {
    public function getExtendedMessage() {
        return 'unzip error: ' . parent::getMessage() . '<!--' . $this->getTraceAsString() . '-->';
    }
}

if (!class_exists('PclZip')) {
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
}
if (!defined('PCLZIP_TEMPORARY_DIR') && !empty($base_upload_dir['basedir'])) {
    define('PCLZIP_TEMPORARY_DIR', FilesHelper::normalize_path($base_upload_dir['basedir']) . '/');
}