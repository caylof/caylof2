<?php
namespace Caylof\Mvc;

use \Caylof\Component;
use \Caylof\Request;
use \Caylof\Response;

/**
 * 控制器基类
 *
 * @package Caylof\Mvc
 * @author caylof
 */
class Controller {

    /**
     * 构造函数
     *
     */
    public function __construct() {
        $this->di = new Component();
        $this->di->request = function() {
            return new Request();
        };
        $this->di->response = function() {
            return new Response();
        };
    }

    /**
     * 获取Request对象
     *
     * @return Request
     */
    public function getRequest() {
        static $request;
        return $request ?: $request = $this->di->request;
    }

    /**
     * 获取Response对象
     *
     * @return Response
     */
    public function getResponse() {
        static $response;
        return $response ?: $response = $this->di->response;
    }

    /**
     * 获取Request对象和Response对象的隐式__get方法
     */
    public function __get($prop) {
        $val = null;
        switch ($prop) {
            case 'request':
                $val = $this->getRequest();
                break;
            case 'response':
                $val = $this->getResponse();
                break;
            default:
                throw new \Exception(sprintf('Property %s is not exist in %s', $prop, __CLASS__));
        }
        return $val;
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
        $url = '/'.trim($url, '/');
        $url .= $query && $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '';
        header('Location: ' . $url);
    }
}
