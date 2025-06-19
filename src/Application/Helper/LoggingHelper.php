<?php

namespace Ceneo\Application\Helper;

class LoggingHelper
{
    public function log($message) {
        $message = '[' . date("Y-m-d H:i:s") . '] ' . $message . "\n";
        file_put_contents(wp_upload_dir()['basedir'] . '/wc-logs/ceneo-' . date("Y-m-d") . '.log', $message, FILE_APPEND);
    }

    public function rotate() {
        $logfiles = glob(wp_upload_dir()['basedir'] . '/wc-logs/ceneo-*');
        $now   = time();

        $this->log('Checking log files to remove.');

        foreach ($logfiles as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 14) { // 14 days
                    unlink($file);
                    $this->log('File ' . $file . ' has been removed.');
                }
            }
        }
    }
}