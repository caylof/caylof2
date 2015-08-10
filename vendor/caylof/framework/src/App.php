<?php
namespace Caylof;

/**
 * 全局组件容器类
 *
 * @package Caylof
 * @author caylof
 */
class App {

    use Traits\Singleton;

    private $di;

    private function __construct() {
        $this->di = new Component();
    }

    public function set($name, \Closure $resolver) {
        $this->di->set($name, $resolver);
    }

    public function get($name) {
        return $this->di->get($name);
    }

    public function __set($name, \Closure $resolver) {
        $this->di->set($name, $resolver);
    }

    public function __get($name) {
        return $this->di->get($name);
    }
}
