<?php
namespace Caylof\Session;

class Session {

    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function set($name, $val) {
        $_SESSION[$name] = $val;
    }

    public function get($name, $default = null) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    public function del($name) {
        unset($_SESSION[$name]);
    }

    public function destroy() {
        $_SESSION = array();
        session_destroy();
        session_regenerate_id();
    }
}
