<?php
namespace Caylof;

/**
 * 简单权限类
 *
 * @package Caylof
 * @author caylof
 */
class Auth {

    /**
     * 权限类计数器
     * 作用在于生成权限值
     *
     * @var int
     */
    protected static $authCount = 0;

    /**
     * 权限名称
     *
     * @var string
     */
    protected $authName;

    /**
     * 权限详细信息
     *
     * @var string
     */
    protected $authMessage;

    /**
     * 权限值
     *
     * @var int 2的N次方
     */
    protected $authValue;

    /**
     * 构造函数
     * 初始化权限名称、权限详细信息以及权限值
     *
     * @param string $authName 权限名称
     * @param string $authMessage 权限详细信息
     */
    public function __construct($authName, $authMessage = '') {
        $this->authName = $authName;
        $this->authMessage = $authMessage;
        $this->authValue = 1 << self::$authCount;
        self::$authCount += 1;
    }

    /**
     * 设置权限详细信息
     *
     * @param string $authMessage
     */
    public function setAuthMessage($authMessage) {
        $this->authMessage = $authMessage;
    }

    /**
     * 获取权限名称
     *
     * @return string
     */
    public function getAuthName() {
        return $this->authName;
    }

    /**
     * 获取权限值
     *
     * @return int
     */
    public function getAuthValue() {
        return $this->authValue;
    }

    /**
     * 获取权限详细信息
     *
     * @return string
     */
    public function getAuthMessage() {
        return $this->authMessage;
    }

    /**
     * 本类不允许对象复制操作
     */
    private function __clone() {
        
    }
}
