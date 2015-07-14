<?php
namespace App\Controllers\Test;

use \Caylof\Mvc\Controller,
    \Caylof\Mvc\View,
    \App\Models\UserModel;

class IndexController extends Controller {

    protected function init() {

    }

    public function index() {
        $request = $this->getRequest();
        $page = $request->get('p', 1);
        //echo '<h1>Hello Caylof</h1>';
        //echo '<p>Page: '.$page.'</p>';
        $this->redirect('/other');
    }

    public function other() {
        $view = new View('test.other');
        $view->assign(array(
            'title' => '测试',
            'body' => '测试测试'
        ));
        $view->output();
        //$this->redirect('/');
    }

    public function listUser($id = 0) {
        $userId = (int)$id;

        $user = new UserModel();
        //$flag = $user->find($userId);
        $flag = $user->where('id', '=', $userId)->get();

        if ($flag) {
            echo '<p>User Id: '.$user->getId().'</p>';
            echo '<p>User Name: '.$user->getName().'</p>';
        } else {
            echo 'User not found';
        }
    }
}
