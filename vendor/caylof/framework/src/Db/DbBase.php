<?php
namespace Caylof\Db;

/**
 * 数据库操作基类
 *
 * @package Caylof\Db
 * @author caylof
 */
abstract class DbBase {

    /**
     * 本类实例对象
     *
     * @var \Caylof\Db\Dbbase
     */
    protected static $instance;

    /**
     * 数据库连接
     *
     * @var \PDO
     */
    protected $handle;

    /**
     * 语句对象
     *
     * @var \PDOStatement
     */
    protected $stmt;

    /**
     * 数据源
     *
     * @var string
     */
    protected $dsn;

    /**
     * 数据源对应的用户名
     *
     * @var string
     */
    protected $user;

    /**
     * 数据源对应的密码
     *
     * @var string
     */
    protected $pass;

    /**
     * 构造函数，外界不能直接实例化，以实现单例模式
     */
    protected function __construct() {
        $this->init();
        $this->connect();
    }

    /**
     * 初始化
     * 加载数据源，用户名以及密码，子类实现该方法
     */
    abstract protected function init();

    /**
     * 获取唯一实例对象（后期绑定）
     */
    public static function getInstance() {
        if (!static::$instance instanceof static) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * 连接数据库
     */
    public function connect() {
        if (!($this->handle instanceof \PDO)) {
            try {
                $this->handle = new \PDO($this->dsn, $this->user, $this->pass);
                $this->handle->exec("set names utf8");
                $this->handle->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                $this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw new DbException('Failed to connect database: ' .$e->getMessage());
            }
        }
    }

    /**
     * 选择要连接的数据库
     *
     * @param string $dbName 数据库名称
     * @throw 选择连接数据库失败信息
     */
    public function selectDb($dbName) {
        try {
            $this->handle->exec('USE ' . $dbName);
        } catch (\PDOException $e) {
            throw new DbException('Failed to select database: ' .$e->getMessage());
        }
    }

    /**
     * 执行一条SQL语句
     *
     +-----------------------------------------------------------------------
       for example:
       query('SELECT * FROM user WHERE id=?', array(1));
     -----------------------------------------------------------------------+
     * @param string $sql
     * @param array|scalar $params
     * @return PDOStatement
     */
    public function query($sql, $params = null) {
        $this->stmt = $this->handle->prepare($sql);
        switch (true) {
            case is_scalar($params):
                $params = [$params];
                break;
            case is_array($params):
                break;
            case is_null($params):
                break;
            default:
                throw new DbException('Param error: "'.$params.'" should be a scalar or linear array');
        }
        $this->stmt->execute($params);
        return $this->stmt;
    }

    /**
     * 获取lastInsertId
     */
    public function lastInsertId () {
        return $this->handle->lastInsertId();
    }

    /**
     * Returns the number of rows affected by the last SQL statement
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * 开启事务处理
     */
    public function beginTransaction() {
        $this->handle->beginTransaction();
    }

    /**
     * 手动提交
     */
    public function commit() {
        $this->handle->commit();
    }

    /**
     * 事务回滚
     */
    public function rollBack() {
        $this->handle->rollBack();
    }

    /**
     * 断开数据库连接
     */
    public function free() {
        $this->handle = null;
    }

    /**
     * 不允许克隆
     */
    protected function __clone() {
        
    }
}
