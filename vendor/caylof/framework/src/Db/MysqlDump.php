<?php
namespace Caylof\Db;

class MysqlDump {

    private $_cmd;

    public function __construct($dbUser, $dbPass, $dbName, $dest, $zip = 'gz') {

        $zipUtils = array('gz' => 'gzip', 'bz2' => 'bzip2');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $zip = 'none';
        }

        if (array_key_exists($zip, $zipUtils)) {
            $filename = $dbName . '.' . date('w') . '.sql' . $zip;
            $this->_cmd = 'mysqldump -u' . $dbUser . ' -p' . $dbPass . ' ' . $dbName . '| ' . $zipUtils[$zip] . ' >' . $dest . '/' . $filename;
        } else {
            $filename = $dbName . '.' . date('w') . '.sql';
            $this->_cmd = 'mysqldump -u' . $dbUser . ' -p' . $dbPass . ' ' . $dbName . ' >' . $dest . '/' . $filename;
        }
    }

    public function backup() {
        system($this->_cmd, $error);
        if ($error) {
            throw new \Exception('Database backup failed: ' . $error);
        }
    }
}
