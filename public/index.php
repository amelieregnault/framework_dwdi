<?php


declare(strict_types=1);

use Framework\Dispatcher;
use Framework\Config\Dotenv;
use Framework\Request;

define ("ROOT_PATH", dirname(__DIR__));


spl_autoload_register(function (string $class_name) {
    $file = str_replace("App/", "", str_replace("\\", "/", $class_name)) . ".php";
    if (file_exists(ROOT_PATH. "/src/" . $file)) {
        require ROOT_PATH . "/src/$file";
    } else if (file_exists(ROOT_PATH . "/" . $file) && str_starts_with("$file", "Framework")) {
        require ROOT_PATH . "/" . $file;
    }
});

set_error_handler("Framework\ErrorHandler::handleError");
set_exception_handler("Framework\ErrorHandler::handleException");

$dotenv = new Dotenv;
$dotenv->load(ROOT_PATH. "/.env");

$request = Request::createFromGlobals();

$router = require ROOT_PATH . "/config/routes.php";
$container = require ROOT_PATH . "/config/services.php";
$middleware = require ROOT_PATH . "/config/middleware.php";

$dispatcher = new Dispatcher($router, $container, $middleware);
$response = $dispatcher->handle($request);
$response->send();