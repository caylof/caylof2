<?php
/*
|--------------------------------------------------------------------------
| Application Service DI
|--------------------------------------------------------------------------
|
| Here is where you can register all of the service for an application.
|
*/

$di = \Caylof\DI::getInstance();

$di->setDefaultService();

$di->set('dbsess', function() use ($di) {
    $db               = $di->get('db');
    $table            = 'session2';
    $fields           = new \StdClass;
    $fields->id       = 'sess_id';
    $fields->data     = 'sess_data';
    $fields->lifeTime = 'sess_lifetime';
    return new \Caylof\Session\SessionStrategy(
        new \Caylof\Session\DbSessionHandler($db, $table, $fields)
    );
});
/*
$di->set('serviceName', function() {
    // return service object
});
*/