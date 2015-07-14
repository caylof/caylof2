<?php
namespace Caylof;

/**
 * 例外处理类
 * 所有例外都记录到本框架应用目录（APP_PATH）下的/storage/logs/error.log文件中
 *
 * @package caylof
 * @author caylof
 */
class Exception extends \Exception {

    /**
     * @var string log文件
     * 抛出例外存储的路径（在本框架应用目录下的相对路径）
     */
    protected $logFile = '/logs/error.log';

    /**
     * 构造函数
     *
     * @param string $message 异常消息内容
     * @param int code 异常代码
     */
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
        $this->logMsg();
    }

    /**
     * 将例外信息写入log文件
     */
    protected function logMsg() {
        error_log('[' . date('Y-m-d H:i:s') . '] ' . get_class($this) . ': ' . $this->getMessage() . ' in ' . $this->getFile() . ' on ' . $this->getLine() . "\n", 3, STORAGE_PATH . $this->logFile);
    }
}
