<?php
namespace Caylof;

/**
 * 组件容器类
 *
 * @package Caylof
 * @author caylof
 */
class Component {

    private $registry = [];

    /**
     * 添加组件到容器
     *
     * @param string $name 组件名称
     * @param Closure $resolver 组件对象闭包
     */
    public function set($name, \Closure $resolver) {
        $this->registry[$name] = $resolver;
    }

    /**
     * 获取组件
     *
     * @param string $name 组件名称
     * @return mixed 组件对象
     * @throw Exception 组件不存在时抛出例外
     */
    public function get($name) {
        if (isset($this->registry[$name])) {
            $resolver = $this->registry[$name];
            return $resolver();
        }
        throw new \Exception(sprintf('%s does not exist in the {%s}', $name, __CLASS__));
    }

    public function __set($name, \Closure $resolver) {
        $this->set($name, $resolver);
    }

    public function __get($name) {
        return $this->get($name);
    }
}
