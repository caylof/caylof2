<?php
namespace Caylof;

class Logger {
    /**
     * @var string log文件
     */
    protected $logFile;

    public function __construct($logFile) {
        $this->logFile = $logFile;
    }

    protected function errLog($msg) {
        error_log($msg . PHP_EOL, 3, $this->logFile);
    }
}
