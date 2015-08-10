<?php
/**
 * Caylof Framework
 *
 * @author caylof
 */

/*
|--------------------------------------------------------------------------
| Tell PHP that we're using UTF-8 strings until the end of the script
|--------------------------------------------------------------------------
*/

mb_internal_encoding('UTF-8');

/*
|--------------------------------------------------------------------------
| Tell PHP that we'll be outputting UTF-8 to the browser
|--------------------------------------------------------------------------
*/

mb_http_output('UTF-8');

/*
|--------------------------------------------------------------------------
| Load bootstrap files
|--------------------------------------------------------------------------
*/

$router = require __DIR__.'/../bootstrap/start.php';

/*
|--------------------------------------------------------------------------
| Start to run route
|--------------------------------------------------------------------------
*/

try {
    $entity = $router->route();
    switch (true) {
        case $entity instanceof \Caylof\Mvc\Controller:
            $entity->response->header();
            echo $entity->response->content;
            break;
        case $entity instanceof \Caylof\Response:
            $entity->header();
            echo $entity->content;
            break;
        default:
            header("Content-type: text/html; charset=utf-8");
            echo $entity;
    }
} catch (\Caylof\Exception\NotFound $e) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    echo 'Sorry, page not found!';
} catch(\Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo $e->getMessage();
}
