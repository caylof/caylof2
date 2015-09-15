<?php
namespace Caylof\Db;

/**
 * 数据库CURD操作类
 *
 * @package Caylof
 * @author caylof
 */
class Query {

    /**
     * 数据库连接
     *
     * @var Db
     */
    private $db;

    /**
     * PDOStatement对象
     *
     * @var \PDOStatement
     */
    private $stmt;

    /**
     * SQL语句
     *
     * @var string
     */
    private $sql;

    /**
     * sql语句中占位符的参数集
     *
     * @var array
     */
    private $params;

    /**
     * 对应数据库中的表
     *
     * @var array
     */
    private $tables;

    /**
     * 缓存join语句
     *
     * @var array
     */
    private $join;

    /**
     * 对应表中字段集
     *
     * @var array
     */
    private $fields;

    /**
     * where条件
     *
     * @var array
     */
    private $where;

    /**
     * 排序条件
     *
     * @var string
     */
    private $order;

    /**
     * 分组条件
     *
     * @var string
     */
    private $group;

    /**
     * limit条件
     *
     * @var string
     */
    private $limit;

    /**
     * sql语句类型
     * 四种类型：select、update、insert、delete
     *
     * @var string
     */
    private $type;


    /**
     * 构造函数
     * 注入“数据库连接”依赖
     *
     * <pre>
     *   $curd = new Query(\Caylof\DbFactory::make());
     * </pre>
     *
     * @param \Caylof\Db\DbBase $db 数据库连接
     */
    public function __construct(DbBase $db) {
        $this->db = $db;
    }

    /**
     * 开始select语句
     *
     * <pre>
     *   // 查询所有字段
     *   $curd->select();
     *
     *   // 查询一个字段
     *   $curd->select('username');
     *
     *   // 查询多个字段
     *   $curd->select(['username', 'passwd']);
     *
     *   // 字段别名（数组中的key值为别名，value值为字段名）
     *   $curd->select(['name' => 'username', 'pwd' => 'passwd']);
     * </pre>
     *
     * @param scalar|array $fields 查询字段，默认为所有字段
     * @return $this
     */
    public function select($fields = ['*']) {
        $this->free();
        $this->type = 'select';
        $fields = is_scalar($fields) ? [$fields] : $fields;
        foreach ($fields as $alias => $field) {
            $this->fields[] = is_numeric($alias) ? $field : join(' AS ', [$field, $alias]);
        }
        return $this;
    }

    /**
     * 开始update语句
     *
     * param array $set 要修改的数据，格式：['字段名' => '修改后的值', ...]
     * @return $this
     */
    public function update(array $set) {
        $this->free();
        $this->type = 'update';
        $this->fields = array_map(function ($v) {
            return "$v=?";
        }, array_keys($set));
        $this->params = array_values($set);
        return $this;
    }

    /**
     * 开始insert语句
     *
     * param array $values 要修改的数据，格式：['字段名' => '要插入的值', ...]
     * @return $this
     */
    public function insert(array $values) {
        $this->free();
        $this->type = 'insert';
        $this->fields = array_keys($values);
        $this->params = array_values($values);
        return $this;
    }

    /**
     * 开始delete语句
     *
     * @return $this
     */
    public function delete() {
        $this->free();
        $this->type = 'delete';
        return $this;
    }

    public function sqlRaw($sql, $params = []) {
        $this->free();
        $this->sql = $sql;
        $this->params = is_scalar($params) ? [$params] : $params;
        return $this;
    }

    /**
     * 设置数据表
     *
     * <pre>
     *   // 一个表
     *   $curd->table('user');
     *
     *   // 多个表
     *   $curd->table(['user', 'role']);
     *
     *   // 表别名（数组中的key值为别名，value值为字段名）
     *   $curd->table(['u' => 'user', 'r' => 'role']);
     *
     *   // 结合select语句
     *   // sql语句相当于【SELECT u.username AS name FROM user AS u】
     *   $curd->select(['name' => 'u.username'])->table(['u' => 'user']);
     * </pre>
     *
     * @param scalar|array $tables 数据表
     * @return $this
     */
    public function table($tables) {
        $tables = is_scalar($tables) ? [$tables] : $tables;
        foreach ($tables as $alias => $table) {
            $this->tables[] = is_numeric($alias) ? $table : join(' AS ', [$table, $alias]);
        }
        return $this;
    }

    /**
     * 设置join条件
     *
     * @param string $type join类型【inner, left, right, full】
     * @param string $table join表
     * @param array  $on join条件
     * @return $this
     */
    public function join($type, $table, array $on) {
        $table = is_scalar($table) ? [$table] : $table;
        $table = is_numeric(key($table)) ? current($table) : join(' AS ', [current($table), key($table)]);
        $this->join[] = join(' ', [strtoupper($type), 'JOIN', $table, 'ON', join('=', $on)]);
        return $this;
    }

    public function orderBy($fields = []) {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->order = join(',', $fields);
        return $this;
    }

    public function groupBy($fields = []) {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->group = join(',', $fields);
        return $this;
    }

    public function limit($start, $count) {
        $this->limit = "LIMIT $start, $count";
        return $this;
    }

    /**
     * 设置where条件语句
     * 多个where方法之间的关系是AND关系
     *
     * <pre>
     *   // ...WHERE id=3
     *   $curd->where(['id' => 3]);
     *
     *   // ...WHERE username='cctv' AND role_id=1
     *   $curd->where(['username' => 'cctv', 'role_id' => 1]);
     *
     *   // 多个where还可以链式写，它们之间的关系相当于AND
     *   $curd->where(['username' => 'cctv'])
     *        ->where(['role_id' => 1]);
     *
     *   // 结合别名的使用
     *   // SQL【SELECT u.username AS u_name, r.name AS r_name FROM user AS u, role AS r WHERE u_name='cctv' AND r.role_id=1】
     *   $curd->select(['u_name' => 'u.username', 'r_name' => 'r.name'])
     *        ->table(['u' => 'user', 'r' => 'role'])
     *        ->where(['u_name' => 'cctv', 'r.role_id' => 1]);
     * </pre>
     *
     * @param array $where 条件数组
     * @return $this
     */
    public function where(array $where) {
        $this->where = array_merge($this->where, array_map(function ($v) {
            return "$v=?";
        }, array_keys($where)));
        $this->params = array_merge($this->params, array_values($where));
        return $this;
    }

    /**
     * 直接设置where语句
     * 用于解决where方法中构造复杂条件语句的不足
     *
     * <pre>
     *   $curd->whereRaw('id>1');
     *   $curd->whereRaw('id>?', 2);
     *   $curd->whereRaw('id>? OR role_id=?', [2, 1]);
     * </pre>
     *
     * @param string $where where条件语句
     * @param scalar|array 条件语句中占位符的参数集
     * @return $this;
     */
    public function whereRaw($where, $params = []) {
        $params = is_scalar($params) ? [$params] : $params;
        $this->params = array_merge($this->params, $params);
        $this->where[] = $where;
        return $this;
    }

    /**
     * 执行sql语句
     *
     * @return $this;
     */
    public function run() {
        $this->createSql();
        //echo '<pre>';
        //var_dump($this->sql, $this->params);
        //echo '</pre>';
        //exit();
        $this->stmt = $this->db->query($this->sql, $this->params);
        return $this;
    }

    /**
     * 获取一条查询结果
     */
    public function one() {
        return $this->type == 'select' ? $this->stmt->fetch() : null;
    }

    /**
     * 获取所有查询结果
     */
    public function all() {
        return $this->type == 'select' ? $this->stmt->fetchAll() : null;
    }

    /**
     * 获取一列查询结果
     */
    public function oneCol($col = 0) {
        return $this->type == 'select' ? $this->stmt->fetchColumn($col) : null;
    }

    public function lastInsertId () {
        return $this->db->lastInsertId();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollBack() {
        $this->db->rollBack();
    }

    /**
     * 释放缓存数据
     */
    private function free() {
        $this->type   = '';
        $this->sql    = '';
        $this->params = [];
        $this->fields = [];
        $this->tables = [];
        $this->join   = [];
        $this->where  = [];
        $this->order  = '';
        $this->group  = '';
        $this->limit  = '';
        $this->stmt   = null;
    }

    /**
     * 生成sql语句
     */
    private function createSql() {
        $fields = join(',', $this->fields);
        $tables = join(',', $this->tables);
        $where = join(' AND ', $this->where);

        switch ($this->type) {
            case 'select':
                $sqlArr = ['SELECT', $fields, 'FROM', $tables];
                $sqlArr = count($this->join) ? array_merge($sqlArr, $this->join) : $sqlArr;
                $sqlArr = $where ? array_merge($sqlArr, ['WHERE', $where]) : $sqlArr;
                $sqlArr = $this->order ? array_merge($sqlArr, ['ORDER BY', $this->order]) : $sqlArr;
                $sqlArr = $this->group ? array_merge($sqlArr, ['GROUP BY', $this->group]) : $sqlArr;
                $sqlArr = $this->limit ? array_merge($sqlArr, [$this->limit]) : $sqlArr;
                $this->sql = join(' ', $sqlArr);
                break;
            case 'update':
                $this->sql = $where ?
                    sprintf("UPDATE %s SET %s WHERE %s", $tables, $fields, $where) :
                    sprintf("UPDATE %s SET %s", $tables, $fields);
                break;
            case 'insert':
                $values = join(',', array_pad([], count($this->fields), '?'));
                $this->sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tables, $fields, $values);
                break;
            case 'delete':
                $this->sql = $where ?
                    sprintf("DELETE FROM %s WHERE %s", $tables, $where) :
                    sprintf("DELETE FROM %s", $tables);
                break;
            default:
                // do nothing
        }
    }
}
