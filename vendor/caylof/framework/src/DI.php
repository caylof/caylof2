<?php
namespace Caylof;

/**
 * 依赖注入容器（dependency injection container）
 *
 * @package Caylof
 * @author caylof
 */
class DI {

    /**
     * 本类实例对象
     *
     * @var \Caylof\DI
     */
    private static $instance;

    /**
     * 依赖注册表
     *
     * @var array
     */
    protected $registry = array();

    /**
     * 外界不能直接实例化
     */
    private function __construct() {
        
    }

    /**
     * 获取本类的唯一实例对象
     */
    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 内置默认服务
     */
    public function setDefaultService() {
        // 注入request请求类
        $this->set('request', function() {
            static $req;
            if (!($req instanceof Request)) {
                $req = new Request();
            }
            return $req;
            //return new Request();
        });

        // 注入数据库操作类
        $this->set('db', function() {
            return Db\DbFactory::make();
        });

        // 注入session类
        $this->set('session', function() {
            return new Session\Session();
        });
    }

    /**
     * 添加依赖到容器
     *
     * @param string $name 依赖名称
     * @param Closure $resolver 依赖闭包
     */
    public function set($name, \Closure $resolver) {
        $this->registry[$name] = $resolver;
    }

    /**
     * 获取依赖
     *
     * @param string $name 依赖名称
     * @return mixed 依赖
     * @throw Exception 依赖不存在时抛出例外
     */
    public function get($name) {
        if (isset($this->registry[$name])) {
            $resolver = $this->registry[$name];
            return $resolver();
        }
        throw new Exception($name . ' does not exist in the DI');
    }

    private function __clone() {
        
    }
}
