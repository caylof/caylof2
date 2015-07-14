<?php
namespace Caylof;

/**
 * 对象实体基类
 *
 * 本基类提供了对属性的getter和setter方法，方便子类不再写繁复的getter和setter方法，子类只用定义所需的属性即可
 * 子类中定义的属性应该为protected
 *
 * @package Caylof
 * @author caylof
 */
class Entity {

    /**
     * 重写魔术方法__call，使其能隐式地调用getter和setter方法
     *
     * @param string $methodName 方法名
     * @param array $args 方法所需参数
     */
    public function __call($methodName, $args) {
        if (preg_match('#^(set|get)([A-Z])(.*)$#', $methodName, $matches)) {
            $property = strtolower($matches[2]) . $matches[3];
            if (!property_exists($this, $property)) {
                throw new Exception('Property ' . $property . ' not exists');
            }
            switch ($matches[1]) {
                case 'set':
                    $this->checkArgs($args, 1, 1, $methodName);
                    return $this->set($property, $args[0]);
                    break;
                case 'get':
                    $this->checkArgs($args, 0, 0, $methodName);
                    return $this->get($property);
                    break;
                default:
                    throw new Exception\BadMethodCall('Method ' . $methodName . ' not exists');
            }
        }
        throw new Exception\BadMethodCall('Method ' . $methodName . ' not exists');
    }

    /**
     * 设置属性
     *
     * @param string $property 属性名
     * @param mixed $value 值
     * @return object 调用该方法的对象，以便链式设置属性操作
     */
    protected function set($property, $value) {
        $this->$property = $value;
        return $this;
    }

    /**
     * 获取属性
     *
     * @param string $property 属性名
     * @return mixed
     */
    protected function get($property) {
        return $this->$property;
    }

    /**
     * 检测方法中所需的参数个数是否超出范围
     *
     * @param array $args 参数集
     * @param int $min 最小个数
     * @param int $max 最大个数
     * @param string $methodName 方法名称
     */
    protected function checkArgs(array $args, $min, $max, $methodName) {
        $argc = count($args);
        if ($argc < $min || $argc > $max) {
            throw new Exception\UnexpectedValue('Method ' . $methodName . ' needs minimaly ' . $min . ' and maximaly ' . $max . ' arguments. But ' . $argc . ' arguments given.');
        }
    }
}
