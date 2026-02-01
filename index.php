<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Fix Request URI for Subdirectory Deployment
|--------------------------------------------------------------------------
| Remove the /ip-call prefix from REQUEST_URI so Laravel can route correctly
*/

$basePath = '/ip-call';
if (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    if (strpos($requestUri, $basePath) === 0) {
        $_SERVER['REQUEST_URI'] = substr($requestUri, strlen($basePath)) ?: '/';
    }
}

// Also fix SCRIPT_NAME for proper URL generation
if (isset($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = __DIR__.'/ip-call-v2/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/ip-call-v2/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.'/ip-call-v2/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
