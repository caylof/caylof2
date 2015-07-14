<?php
namespace Caylof\Db;

/**
 * 数据库工场类
 *
 * @package Caylof\Db
 * @author caylof
 */
final class DbFactory {

    /**
     * MySQL类型
     *
     * @const string
     */
    const TYPE_MYSQL = 'MYSQL';

    /**
     * SQLlite类型
     *
     * @const string
     */
    const TYPE_SQLLITE = 'SQLITE';

    /**
     * 私有构造函数
     */
    private function __construct() {
        
    }

    /**
     * 获取数据库操作类对象
     *
     * @todo 获取sqlite类型的数据库（待完成）
     * @param string $type
     * @return mixed
     */
    public static function make($type = self::TYPE_MYSQL) {
        switch ($type) {
            case self::TYPE_MYSQL:
                $db = Mysql::getInstance();
                break;
            default:
                $db = Mysql::getInstance();
        }
        return $db;
    }

    /**
     * 不允许克隆
     */
    private function __clone() {
        
    }
}
