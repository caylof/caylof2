<?php
namespace App\Services\Validators;

/**
 * 模型基础类
 *
 * @package App\Services
 * @author caylof
 */
abstract class Validator {

    /**
     * 存储错误信息
     *
     * @var array
     */
    protected $_error = [];

    /**
     * 存储label信息
     *
     * @var array
     */
    protected $_label = [];

    /**
     * 验证规则，子类需重写该方法
     *
     * @return array
     * [
     *     [
     *         'field'  => '验证属性',
     *         'method' => '验证方法名',
     *         'params' => '验证方法参数',
     *         'error'  => '验证错误信息'
     *     ],
     * ]
     *
     * “验证属性”可以是一个字符串表示，多个“验证属性”需用数组表示
     * “验证方法名”必须为本类或子类中的方法，验证方法返回结果类型应为布尔
     * “验证方法参数”为数组类型，且对应“验证方法参数”中的“验证属性”以后的参数
     * “验证错误信息”为字符串类型
     */
    abstract public function rule();

    /**
     * 加载数据给属性
     *
     * @param array $data “键值对”数组
     */
    public function load(array $data) {
        foreach ($data as $prop => $val) {
            if (property_exists($this, $prop)) {
                $this->$prop = $val;
            }
        }
    }

    /**
     * 将公共属性作为数组
     */
    public function toArray() {
        $refClass = new \ReflectionClass($this);
        $props =  $refClass->getDefaultProperties();
        $arr = [];
        foreach ($props as $prop => $val) {
            if ($refClass->getProperty($prop)->isPublic()) {
                $arr[$prop] = $this->$prop;
            }
        }
        return $arr;
    }

    /**
     * label读写方法
     * 若第一个参数为“键值对数组”，那么第二个参数应该为null，此时为“写多个label”
     * 若第一个参数为“标量”，且第二个参数为“string类型”，此时为“写一个label”
     * 若第一个参数为“标量”，且没有第二个参数，此时为“读一个label”
     *
     * @param array|scalar $field
     * @param null|string $value
     * @return null|string
     */
    public function label($field, $value = null) {
        if (is_array($field)) {
            $this->_label = array_merge($this->_label, $field);
        } else {
            if (isset($value)) {
                $this->_label[$field] = $value;
            } else {
                return $this->_label[$field];
            }
        }
    }

    /**
     * 获取label字符串
     */
    public function getLabelString(array $fields) {
        $ret = [];
        foreach ($fields as $field) {
            if (isset($this->_label[$field])) {
                $ret[] = $this->_label[$field];
            } else {
                if (property_exists($this, $field)) {
                    $ret[] = $field;
                }
            }
        }
        return join(',', $ret);
    }

    /**
     * 进行验证规则
     *
     * @return boolean
     */
    public function validate($rules = []) {
        $rules = count($rules) ? $rules : $this->rule();
        foreach ($rules as $rule) {
            $field  = isset($rule['field']) ? $rule['field'] : '';
            $method = isset($rule['method']) ? $rule['method'] : '';
            $params = isset($rule['params']) ? $rule['params'] : [];
            $msg    = isset($rule['error']) ? $rule['error'] : '';

            if (is_array($field)) {
                $subRules = [];
                foreach ($field as $v) {
                    $subRules[] = [
                        'field' => $v, 'method' => $method, 'params' => $params, 'error' => $msg
                    ];
                }
                if (!$this->validate($subRules)) {
                    return false;
                }
            } else {
                $what = [];
                foreach ($splits = explode('+', $field) as $split) {
                    $what[] = property_exists($this, $split) ? $this->$split : null;
                }
                $what = array_merge($what, $params);
                if ($method instanceof \Closure && !call_user_func_array($method, $what)) {
                    $this->_error = [$splits, $msg];
                    return false;
                } else if (is_string($method)) {
                    if (method_exists($this, $method) && !(new \ReflectionMethod($this, $method))->invokeArgs($this, $what)) {
                        $this->_error = [$splits, $msg];
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * 获取错误信息
     *
     * @return array ['错误属性', '错误信息']
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * 判断字符串是否为空
     *
     * @return boolean
     */
    public function isRequired($what) {
        return !!strlen($what);
    }

    /**
     * 判定字符串是否在指定长度内
     *
     * @return boolean
     */
    public function isInLength($what, $min, $max) {
        return (strlen($what) >= $min) && (strlen($what) <= $max);
    }
}
