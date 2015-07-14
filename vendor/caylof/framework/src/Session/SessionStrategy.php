<?php
namespace Caylof\Session;

class SessionStrategy extends Session {

    private $_strategy;

    public function __construct(\SessionHandlerInterface $strategy) {
        $this->_strategy = $strategy;
        session_set_save_handler(
            array($this->_strategy, 'open'),
            array($this->_strategy, 'close'),
            array($this->_strategy, 'read'),
            array($this->_strategy, 'write'),
            array($this->_strategy, 'destroy'),
            array($this->_strategy, 'gc')
        );
        session_start();
    }
}
