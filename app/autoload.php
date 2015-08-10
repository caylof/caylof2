<?php
/*
|--------------------------------------------------------------------------
| Load vendor autoloadClass
|--------------------------------------------------------------------------
*/

$loader = require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Add namespace in the app
|--------------------------------------------------------------------------
| you can add other namespace that you want there
|
*/

$loader->addNamespace('App\Controllers', __DIR__.'/controllers')
       ->addNamespace('App\Models', __DIR__.'/models')
       ->addNamespace('App\Services', __DIR__.'/services');
