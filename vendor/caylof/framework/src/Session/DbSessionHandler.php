<?php
namespace Caylof\Session;

/*
session数据表
CREATE TABLE `test`.`session2` (
  `sess_id` CHAR(40) NOT NULL,
  `sess_data` VARCHAR(200) NOT NULL,
  `sess_lifetime` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`sess_id`)
)
ENGINE = InnoDB;
*/

class DbSessionHandler implements \SessionHandlerInterface {

    private $db;

    private $table;

    private $fields;

    private $maxLifeTime;

    public function __construct($db, $table, $fields) {
        $this->db = $db;
        $this->table = $table;
        $this->fields = $fields;
        $this->maxLifeTime = get_cfg_var('session.gc_maxlifetime');
        /*session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );*/
    }

    public function open($savePath, $sessionId) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        $sql = "SELECT {$this->fields->data} FROM {$this->table} WHERE {$this->fields->id} = ? LIMIT 1";
        $res = $this->db->query($sql, $sessionId)->fetch();
        return empty($res) ? '' : $res[$this->fields->data];
    }

    public function write($sessionId, $sessionData) {
        $sql = "SELECT count(*) FROM {$this->table} WHERE {$this->fields->id} = ?";
        $res = $this->db->query($sql, $sessionId)->fetchColumn();
        $sql = $res ? 
            "UPDATE {$this->table} SET {$this->fields->data} = ?, {$this->fields->lifeTime} = ? WHERE {$this->fields->id} = ?" : 
            "INSERT INTO {$this->table} ({$this->fields->data}, {$this->fields->lifeTime}, {$this->fields->id}) VALUES (?, ?, ?)";
        $lifeTime = time() + $this->maxLifeTime;
        $this->db->query($sql, [$sessionData, $lifeTime, $sessionId]);
        return $this->db->rowCount() > 0;
    }

    public function destroy($sessionId) {
        //$sql = "DELETE FROM {$this->table} WHERE {$this->fields->id} = ?";
        $sql = sprintf("DELETE FROM %s WHERE %s = ?", $this->table, $this->fields->id);
        $this->db->query($sql, $sessionId);
        return $this->db->rowCount() > 0;
    }

    public function gc($maxLifeTime) {
        //$sql = "DELETE FROM {$this->table} WHERE {$this->fields->lifeTime} < ?";
        $sql = sprintf("DELETE FROM %s WHERE %s < ?", $this->table, $this->fields->lifeTime);
        $this->db->query($sql, time());
        return $this->db->rowCount() > 0;
    }

    /*public function __destruct() {
        session_write_close();
    }*/
}