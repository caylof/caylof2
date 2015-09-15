<?php
namespace Caylof\Mvc;

use Caylof\Component;

/**
 * 简单模型类
 *
 * @package Caylof\Mvc
 * @author caylof
 */
class Model {

    public function __construct() {
        $this->di = new Component();
        $this->di->db = function() {
            return \Caylof\Db\DbFactory::make();
        };
        $this->di->query = function() {
            return new \Caylof\Db\Query($this->db);
        };
    }

    public function getDb() {
        static $db;
        return $db ?: $db = $this->di->db;
    }

    public function getQuery() {
        static $query;
        return $query ?: $query = $this->di->query;
    }

    public function __get($prop) {
        $val = null;
        switch ($prop) {
            case 'db':
                $val = $this->getDb();
                break;
            case 'query':
                $val = $this->getQuery();
                break;
            default:
                throw new \Exception(sprintf('Property %s is not exist in %s', $prop, __CLASS__));
        }
        return $val;
    }
}
