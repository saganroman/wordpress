<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThemlerFilesUtility {

    public static function emptyDir($dir, $hard = false) {
        if (!file_exists($dir) || !is_readable($dir)) {
            return;
        }

        if (is_file($dir) && false === @unlink($dir)) {
            throw new Exception("Can't unlink $dir");
        }

        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                $path = $dir . '/' . $object;
                if (strtolower(filetype($path)) == 'dir') {
                    self::emptyDir($path, true);
                } else if (false === @unlink($path)) {
                    throw new Exception("Can't unlink $path");
                }
            }
        }
        reset($objects);
        if ($hard && false === @rmdir($dir))
            throw new Exception("Can't rmdir $dir");
    }

    public static function copyRecursive($source, $destination) {
        if (is_file($source)) {
            if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0777, true)) {
                return;
            }
            copy($source, $destination);

        } else if(is_dir($source)) {
            if (!is_dir($destination) && !mkdir($destination)) {
                return;
            }
            if ($dh = opendir($source)) {
                while (($file = readdir($dh)) !== false) {
                    if('.' == $file || '..' == $file) {
                        continue;
                    }
                    self::copyRecursive($source . '/' . $file, $destination . '/' . $file);
                }
                closedir($dh);
            }
        }
    }

    public static function createDir($dir) {
        if (!is_dir($dir) && false === @mkdir($dir, 0777, true))
            throw new Exception("Can't mkdir $dir");
    }

    public static function write($path, $content) {
        if (false === file_put_contents($path, $content))
            throw new Exception("Can't file_put_contents $path");
    }

    public static function createZip($source, $dest) {
        $source = self::normalizePath($source);
        $dest = self::normalizePath($dest);

        $zip = new ZipArchive();
        $zip->open($dest, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = self::normalizePath($file->getRealPath());
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    public static function extractZip($source, $dest) {
        $source = self::normalizePath($source);
        $dest = self::normalizePath($dest);

        $zip = new ZipArchive;
        if ($zip->open($source) !== true) {
            throw new Exception('ZipArchive open error');
        }
        if ($zip->extractTo($dest) !== true) {
            throw new Exception('ZipArchive extractTo error');
        }
        $zip->close();
    }

    public static function readfile($path) {
        if (false === @readfile($path))
            throw new Exception("Can't readfile $path");
    }

    public static function removeFile($path) {
        if (is_file($path) && false === @unlink($path))
            throw new Exception("Can't remove $path");
    }

    public static function rename($from, $to) {
        if (false === rename($from, $to))
            throw new Exception("Can't rename from $from to $to");
    }

    public static function normalizePath($path) {
        return str_replace("\\", "/", $path);
    }
}