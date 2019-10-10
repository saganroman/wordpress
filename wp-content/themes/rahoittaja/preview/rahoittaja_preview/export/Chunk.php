<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Chunk {

    public $UPLOAD_PATH;

    private $_lastChunk = null;
    private $_chunkFolder = '';
    private $_lockFile = '';
    private $_isLast = false;

    public function __construct() {
        ProviderLog::start('Chunk save');
        $this->UPLOAD_PATH = dirname(__FILE__) . '/chunks/';
        $this->_chunkFolder = $this->UPLOAD_PATH . 'default';
    }

    public function save($info) {
        $this->validate($info);

        $this->_lastChunk = $info;
        $this->_chunkFolder = $this->UPLOAD_PATH . $info['id'];
        $this->_lockFile = $this->_chunkFolder . '/lock';

        FilesHelper::create_dir($this->_chunkFolder);

        $f = fopen($this->_lockFile, 'c');

        if (!flock($f, LOCK_EX))
            throw new PermissionDeniedException("Couldn't lock the file " . $this->_lockFile);

        $chunks = array_diff(scandir($this->_chunkFolder), array('.', '..', 'lock'));

        if ((int) $this->_lastChunk['total'] === count($chunks) + 1) {
            $this->_isLast = true;
        }

        if (!empty($this->_lastChunk['blob'])) {
            if (empty($_FILES['content']['tmp_name'])) {
                ProviderLog::end('Chunk save');
                return new WP_Error('bad_request', isset($_FILES['content']['error']) ? $this->_getUploadErrorMessage($_FILES['content']['error']) : '');
            }

            move_uploaded_file(
                $_FILES['content']['tmp_name'],
                $this->_chunkFolder . '/' . (int) $info['current']
            );
        } else {
            file_put_contents($this->_chunkFolder . '/' . (int) $info['current'], $info['content']);
        }

        flock($f, LOCK_UN);
        ProviderLog::end('Chunk save');
        return true;
    }

    public function last() {
        return $this->_isLast;
    }

    public function complete() {
        ProviderLog::start('Chunk complete');
        $content = '';
        for ($i = 1, $count = (int) $this->_lastChunk['total']; $i <= $count; $i++) {
            if (!file_exists($this->_chunkFolder . "/$i")) {
                $this->clear_chunk_directory();
                throw new Exception('Missing chunk #' . $i . ' : ' . implode(' / ', scandir($this->_chunkFolder)));
            }
            $chunk = file_get_contents($this->_chunkFolder . "/$i");
            if (false === $chunk)
                throw new PermissionDeniedException($this->_chunkFolder . "/$i");
            $data = $chunk;

            if (!empty($this->_lastChunk['encode']) || !empty($this->_lastChunk['zip'])) {
                $data = base64_decode($data);
            }
            $content .= $data;
        }
        if (!empty($this->_lastChunk['encode'])) {
            $content = rawurldecode($content);
        } else if (!empty($this->_lastChunk['zip'])) {
            $content = $this->unzipData($content);
        }
        $content = empty($this->_lastChunk['encode']) ? $content : rawurldecode($content);
        $this->clear_chunk_directory();
        ProviderLog::end('Chunk complete');
        return $content;
    }

    public function unzipData($str) {
        ProviderLog::start('unzipData');

        $archive_file = $this->UPLOAD_PATH . 'data.zip';
        if (false === @file_put_contents($archive_file, $str)) {
            throw new UnzipException($archive_file);
        }

        $archive = new PclZip($archive_file);
        $result_path = $this->UPLOAD_PATH . 'result';

        if (0 == $archive->extract(PCLZIP_OPT_PATH, $result_path)) {
            throw new UnzipException('Extract error: ' . $archive->errorInfo(true));
        }
        $result = @file_get_contents($result_path . '/data');
        if ($result === false) {
            throw new UnzipException($result_path . '/data');
        }

        ProviderLog::end('unzipData');
        return $result;
    }

    private function validate($info) {
        if (empty($info['id']))
            throw new Exception('Invalid id');
        if (!isset($info['total']) || (int) $info['total'] < 1)
            throw new Exception('Invalid chunks total');
        if (!isset($info['current']) || (int) $info['current'] < 1)
            throw new Exception('Invalid current chunk number');
        if (empty($_FILES['content']) && empty($info['content']))
            throw new Exception('Invalid content');
    }

    public function clear_chunk_directory() {
        ProviderLog::start('Chunk clear');
        FilesHelper::empty_dir($this->UPLOAD_PATH, true);
        ProviderLog::end('Chunk clear');
    }

    private function _getUploadErrorMessage($error_core) {
        switch ($error_core) {
            case UPLOAD_ERR_OK:
                return 'There is no error, the file uploaded with success.';
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.';
        }
        return $error_core;
    }
}

class ChunkedUploader {

    public static function process_action($callback) {
        $filename = $_REQUEST['filename'];
        $is_last = $_REQUEST['last'];
        $content_range = $_SERVER['HTTP_CONTENT_RANGE'];
        $base_upload_dir = wp_upload_dir();

        if (false !== $base_upload_dir['error']) {
            return array(
                'status' => 'error',
                'message' => 'Upload folder error: ' . $base_upload_dir['error']
            );
        }
        if (!isset($_FILES['chunk']) || !file_exists($_FILES['chunk']['tmp_name'])) {
            return array(
                'status' => 'error',
                'message' => 'Empty chunk data'
            );
        }
        if (!$content_range && !$is_last) {
            return array(
                'status' => 'error',
                'message' => 'Empty Content-Range header'
            );
        }
        if (!$filename) {
            return array(
                'status' => 'error',
                'message' => 'Empty file name'
            );
        }

        $tmp_path = $base_upload_dir['basedir'] . '/' . $filename;
        $range_begin = 0;

        if ($content_range) {
            list($range, $total) = explode('/', str_replace('bytes ', '', $content_range));
            list($range_begin, $range_end) = explode('-', $range);
        }

        $file = fopen($tmp_path, 'c');

        if (flock($file, LOCK_EX)) {
            fseek($file, (int) $range_begin);
            fwrite($file, file_get_contents($_FILES['chunk']['tmp_name']));
            flock($file, LOCK_UN);
            fclose($file);
        }

        if ($is_last) {
            return call_user_func($callback, array(
                'filename' => $filename
            ));
        }

        return array('status' => 'processed');
    }
}