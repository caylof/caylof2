<?php
/*
|--------------------------------------------------------------------------
| Autoload the vendor autoloadClass
|--------------------------------------------------------------------------
| also you can add other namespace that you want there
|
*/

require __DIR__.'/Psr4AutoloadClass.php';
$loader = new Vendor\Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('Caylof', __DIR__.'/caylof/framework/src');

return $loader;
