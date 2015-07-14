<?php
namespace App\Controllers\Error;

use \Caylof\Mvc\Controller;

class NotFound extends Controller {

    protected function init() {
        
    }

    public function index() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        echo 'Sorry, page not found!';
    }
}
