<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
|
*/

$router = \Caylof\Router::getInstance();
$router->get('/', 'App\Controllers\Test\IndexController@index')
       ->get('/other', 'App\Controllers\Test\IndexController@other')
       ->get('/list/{uid}', 'App\Controllers\Test\IndexController@listUser', '(\d+)');

return $router;
