<?php
namespace Caylof\Mvc;

/**
 * 控制器基类
 *
 * @package Caylof\Mvc
 * @author caylof
 */
abstract class Controller {

    /**
     * 依赖
     *
     * var \Caylof\DI
     */
    protected $di;

    /**
     * 构造函数
     */
    public function __construct() {
        $this->init();
    }

    /**
     * 初始化
     * 子类实现该方法来对应用控制器进行初始化设置
     */
    abstract protected function init();

    /**
     * 注入依赖类
     *
     * @param \Caylof\DI $di 
     */
    public function setDI(\Caylof\DI $di) {
        $this->di = $di;
    }

    /**
     * 获取request
     *
     * @return \Caylof\Request
     */
    public function getRequest() {
        return $this->di->get('request');
    }

    /**
     * url跳转
     *
     +----------------------------------------------------------
       for example:
       redirect('test/user/list');
       redirect(array('test', 'user', 'list'));
     ----------------------------------------------------------+
     * @param string|array $url
     * @param bool $query 是否带上跳转前的路径的query参数
     */
    public function redirect($url, $query = false) {
        if (is_array($url)) {
            $url = array_map('trim', $url);
            $url = join('/', $url);
        }
        $url = './'.trim($url, '/');
        $url .= $query && $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '';
        header('Location: ' . $url);
    }
}
