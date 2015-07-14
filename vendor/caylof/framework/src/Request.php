<?php
namespace Caylof;

/**
 * 简单HTTP请求类
 *
 * @package Caylof
 * @author caylof
 */
class Request {

    /**
     * get数据
     *
     * @var Closure
     */
    private $_get;

    /**
     * post数据
     *
     * @var Closure
     */
    private $_post;

    /**
     * request数据（get和post数据）
     *
     * @var Closure
     */
    private $_request;

    /**
     * files数据（文件上传数据）
     *
     * @var Closure
     */
    private $_file;

    /**
     * 构造函数
     * 初始化各个数据
     */
    public function __construct() {
        $this->_get = function() {
            return $_GET;
        };
        $this->_post = function() {
            return $_POST;
        };
        $this->_request = function() {
            return array_merge($_GET, $_POST);
        };
        $this->_files = function() {
            return $_FILES;
        };
    }

    /**
     * 获取get数据
     * 如果没有参数，则表示获取整个get数组数据
     * 如果有第一个参数，该参数则作为get数组中相应key，并返回对应的get数组的值
     * 如果有第二个参数，则在get数组中不存在对应key值，返回些值
     *
     +---------------------------------------------------------------------
       for example:
       $this->get(); 获取整个get数组数据
       $this->get('page'); 获取get数据中的page的值，如果没有page，则返回null
       $this->get('page', 1); 获取get数据中的page的值，如果没有page，则返回1
     ---------------------------------------------------------------------+
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function get($param = null, $default = null) {
        return $this->_fetch('get', $param, $default);
    }

    /**
     * 获取post数据
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function post($param = null, $default = null) {
        return $this->_fetch('post', $param, $default);
    }

    /**
     * 获取reqest数据
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function req($param = null, $default = null) {
        return $this->_fetch('request', $param, $default);
    }

    /**
     * 获取files数据
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function files($param = null, $default = null) {
        return $this->_fetch('files', $param, $default);
    }

    /**
     * 简单清理数据
     * 目前只做了 trim 清理
     *
     * @param array $data
     * @return array
     */
    private function _clean(array $data) {
        $ret = array();
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $ret[$key] = $this->_clean($val);
            } else {
                $ret[$key] = trim($val);
            }
        }
        return $ret;
    }

    /**
     * 获取数据
     *
     * @param string $type
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    private function _fetch($type, $param = null, $default = null) {
        switch ($type) {
            case 'get':
                $raw = $this->_get;
                break;
            case 'post':
                $raw = $this->_post;
                break;
            case 'request':
                $raw = $this->_request;
                break;
            case 'files':
                $raw = $this->_files;
                break;
            default:
                $raw = $this->_request;
        }
        $raw = $this->_clean($raw());
        return $param === null ? $raw : (isset($raw[$param]) ? $raw[$param] : $default);
    }
}
