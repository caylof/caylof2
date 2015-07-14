<?php
namespace Caylof\Mvc;

/**
 * 简单模型类
 *
 * @package Caylof\Mvc
 * @author caylof
 */
class Model {

    /**
     * 映射到数据库中的“表”名（子类必须重写）
     *
     * @var string
     */
    protected $table;

    /**
     * 映射到“表”中的各项“列”名（子类必须重写）
     *
     * @var array 格式（['模型中的属性名' => '数据库表中列字段名']）
     */
    protected $fieldsMapping = [];

    /**
     * “表”中的“主键”名（子类必须重写）
     *
     * @var string|array
     */
    protected $pk;

    /**
     * “表”中的“自增长列”名（若有自增长列子类必须重写）
     *
     * @var stirng
     */
    protected $autoIncrent;

    /**
     * 存储字段映射的属性
     *
     * @var array
     */
    protected $_data;

    /**
     * 存储sql预处理语句
     *
     * @var string
     */
    protected $_sql;

    /**
     * 存储sql预处理语句中的占位参数集
     *
     * @var array
     */
    protected $_sqlParams;

    /**
     * PDOStatement对象
     *
     * @var \PDOStatement
     */
    protected $_stmt;

    /**
     * 构造函数
     */
    public function __construct() {
        $this->_initData();
    }

    /**
     * 根据主键查找
     *
     * @param scalar|array $pkVal
     */
    public function find($pkVal) {
        $what  = join(',', array_values($this->fieldsMapping));
        $pk    = is_array($this->pk) ? $this->pk : [$this->pk];
        $where = join(' AND ', array_map(function($v) {
            return "$v = ?";
        }, $pk));
        $this->_sql = sprintf("SELECT %s FROM %s WHERE %s", $what, $this->table, $where);
        $this->_sqlParams = $pkVal;
        return $this->get();
    }

    /**
     * 设置一个where条件
     *
     +--------------------------------------
       for example:
        $this->where('name', '=', 'cctv');
     --------------------------------------+
     *
     * @param where条件
     */
    public function where() {
        $what = join(',', array_values($this->fieldsMapping));
        $clause = func_get_args();
        $this->_sqlParams = array_pop($clause);
        $where = join(' ', $clause);
        $this->_sql = sprintf("SELECT %s FROM %s WHERE %s ?", $what, $this->table, $where);
        return $this;
    }

    /**
     * 设置一个where条件
     *
     +------------------------------------------------
       for example:
        $this->whereRaw('id > ? AND id < ?', [1, 5]);
     ------------------------------------------------+
     *
     * @param string $where
     * @param scalar|array $params
     */
    public function whereRaw($where, $params = null) {
        $what = join(',', array_values($this->fieldsMapping));
        $this->_sqlParams = is_scalar($params) ? [$params] : $params;
        $this->_sql = sprintf("SELECT %s FROM %s WHERE %s", $what, $this->table, $where);
        return $this;
    }

    /**
     * 获取一条数据到模型
     */
    public function get() {
        $this->_execute();
        if ($res = $this->_stmt->fetch()) {
            foreach (array_keys($this->fieldsMapping) as $field) {
                $this->_data[$field] = $res[$this->fieldsMapping[$field]];
            }
            return true;
        }
        return false;
    }

    /**
     * 获取所有数据
     *
     * @return array
     */
    public function all() {
        $this->_execute();
        return $this->_stmt->fetchAll();
    }

    /**
     * 修改
     */
    public function update() {
        $pk = is_array($this->pk) ? $this->pk : [$this->pk];
        $set = join(',', array_reverse(array_map(function($v) {
            return "$v = ?";
        }, array_filter(array_values($this->fieldsMapping), function($vv) use ($pk) {
            return !in_array($vv, $pk);
        }))));
        $where = join(' AND ', array_reverse(array_map(function($v) {
            return "$v = ?";
        }, $pk)));
        $this->_sql = sprintf("UPDATE %s SET %s WHERE %s", $this->table, $set, $where);
        $params = [];
        foreach ($this->_data as $k => $v) {
            in_array($this->fieldsMapping[$k], $pk) ? array_push($params, $v) : array_unshift($params, $v);
        }
        $this->_sqlParams = count($params) ? $params : null;
        $this->_execute();
        return $this->_db->rowCount();
    }

    /**
     * 插入
     */
    public function insert() {
        $pk = is_array($this->pk) ? $this->pk : [$this->pk];
        $notInsertFields = in_array($this->autoIncrent, $pk) ? $pk : array_merge($pk, [$this->autoIncrent]);
        $insertFields = array_filter(
            array_values($this->fieldsMapping), 
            function($v) use ($notInsertFields) {
                return !in_array($v, $notInsertFields);
            }
        );
        $what = join(',', $insertFields);
        $values = join(',', array_pad([], count($insertFields), '?'));
        $this->_sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, $what, $values);
        $params = [];
        foreach ($this->_data as $k => $v) {
            if (!in_array($this->fieldsMapping[$k], $notInsertFields)) {
                array_push($params, $v);
            }
        }
        $this->_sqlParams = count($params) ? $params : null;
        $this->_execute();
        $rowCount =  $this->_db->rowCount();
        if ($rowCount > 0) {
            $autoIncrentKey = array_search($this->autoIncrent, $this->fieldsMapping);
            if ($autoIncrentKey !== false) {
                $this->_set($autoIncrentKey, $this->_db->lastInsertId());
            }
        }
        return $rowCount;
    }

    /**
     * 删除
     */
    public function delete() {
        $pk = is_array($this->pk) ? $this->pk : [$this->pk];
        $where = join(' AND ', array_map(function($v) {
            return "$v = ?";
        }, $pk));
        $this->_sql = sprintf("DELETE FROM %s WHERE %s", $this->table, $where);
        $params = [];
        foreach ($pk as $v) {
            $mappingKey = array_search($v, $this->fieldsMapping);
            if ($mappingKey !== false) {
                array_push($params, $this->_get($mappingKey));
            }
        }
        $this->_sqlParams = count($params) ? $params : null;
        $this->_execute();
        $rowCount = $this->_db->rowCount();
        if ($rowCount > 0) {
            $this->_initData();
        }
        return $rowCount;
    }

    /**
     * getter、setter方法
     */
    public function __call($methodName, $args) {
        if (preg_match('#^(set|get)([A-Z])(.*)$#', $methodName, $matches)) {
            $field = strtolower($matches[2]) . $matches[3];
            if (!isset($this->fieldsMapping[$field])) {
                throw new \Exception('Field{' . $field . '} not exists in the Model{'.static::_getTrueClass().'}');
            }
            switch ($matches[1]) {
                case 'set':
                    $this->_checkArgs($args, 1, 1, $methodName);
                    return $this->_set($field, $args[0]);
                    break;
                case 'get':
                    $this->_checkArgs($args, 0, 0, $methodName);
                    return $this->_get($field);
                    break;
                default:
                    throw new Exception\BadMethodCall('Method ' . $methodName . ' not exists');
            }
        }
        throw new Exception\BadMethodCall('Method ' . $methodName . ' not exists');
    }

    protected function _set($field, $value) {
        $this->_data[$field] = $value;
        return $this;
    }

    protected function _get($field) {
        return $this->_data[$field];
    }

    protected function _checkArgs(array $args, $min, $max, $methodName) {
        $argc = count($args);
        if ($argc < $min || $argc > $max) {
            throw new Exception\BadMethodCall('Method ' . $methodName . ' needs minimaly ' . $min . ' and maximaly ' . $max . ' arguments. But ' . $argc . ' arguments given.');
        }
    }

    protected static function _getTrueClass() {
        return __CLASS__;
    }

    /**
     * 初始化各个映射的属性值为null
     */
    protected function _initData() {
        foreach (array_keys($this->fieldsMapping) as $field) {
            $this->_data[$field] =  null;
        }
    }

    /**
     * 执行sql语句操作
     */
    protected function _execute() {
        $db = \Caylof\Db\DbFactory::make();
        $this->_stmt = $db->query($this->_sql, $this->_sqlParams);
    }
}
