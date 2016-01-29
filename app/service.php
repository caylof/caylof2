<?php
/*
|--------------------------------------------------------------------------
| Application Service DI
|--------------------------------------------------------------------------
|
| Here is where you can register all of the service for an application.
|
*/

$app = Caylof\App::getInstance();

// 注入数据库操作类
$app->set('db', function() {
    return Caylof\Db\DbFactory::make();
});

// 注入session类
$app->set('session', function() {
    return new Caylof\Session\Session();
});

/*
// 注入mysql数据库session类
$app->set('dbsess', function() use ($app) {
    $db               = $di->get('db');
    $table            = 'session2';
    $fields           = new \StdClass;
    $fields->id       = 'sess_id';
    $fields->data     = 'sess_data';
    $fields->lifeTime = 'sess_lifetime';
    return new Caylof\Session\SessionStrategy(
        new Caylof\Session\DbSessionHandler($db, $table, $fields)
    );
});
*/
