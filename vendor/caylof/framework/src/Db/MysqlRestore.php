<?php
namespace Caylof\Db;

class MysqlRestore {

    private $_cmd;

    public function __construct($dbUser, $dbPass, $dbName, $filepath) {
        $this->_cmd = 'mysql -u' . $dbUser . ' -p' . $dbPass . ' ' . $dbName . ' < ' . $filepath;
    }

    public function restore() {
        system($this->_cmd, $error);
        if ($error) {
            throw new \Exception('Databae restore failed: ' . $error);
        }
    }
}
