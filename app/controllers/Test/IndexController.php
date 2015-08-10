<?php
namespace App\Controllers\Test;

use \Caylof\Mvc\Controller,
    \Caylof\Mvc\View;

class IndexController extends Controller {

    public function index() {
        $page = $this->request->get('p', 1);
        $res = '<h1>Hello Caylof</h1>';
        $res .= '<p>Page: '.$page.'</p>';

        $this->response->content = $res;
        return $this;
    }

    public function other() {
        $view = new View('test.other');
        $view->assign(array(
            'title' => '测试',
            'body' => '测试测试'
        ));

        $this->response->content = $view->render();
        return $this;
        //$this->redirect('/');
    }

    public function listUser($userId) {
        //$this->response->type = 'json';
        $this->response->content = json_encode([
            'id' => $userId,
            'name' => '中国人'
        ], JSON_UNESCAPED_UNICODE);

        return $this->response;
    }
}
