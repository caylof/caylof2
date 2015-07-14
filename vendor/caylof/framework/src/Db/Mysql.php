<?php
namespace Caylof\Db;

/**
 * mysql数据库操作类
 *
 * @package Caylof\Db
 * @author caylof
 */
class Mysql extends DbBase {

    /**
     * 初始化数据源，用户名以及密码
     */
    protected function init() {
        $configs = parse_ini_file(APP_PATH.'/configs.ini', true);
        $this->dsn = $configs['mysql']['db_source'];
        $this->user = $configs['mysql']['db_user'];
        $this->pass = $configs['mysql']['db_password'];
    }
}
