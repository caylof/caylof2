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
->get('/other/yet', 'App\Controllers\Test\IndexController@other')
->get('/list/{uid}', 'App\Controllers\Test\IndexController@listUser', '(\d+)')
->get('/test', function() {
    return '中中中国国国';
});

return $router;
