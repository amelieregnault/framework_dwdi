<?php

$router = new Framework\Router();
$router->add("/{controller}/{id:\d+}/destroy", ["action" => "destroy", "method"=>"post"]);
$router->add("/{controller}/{id:\d+}/show", ["action" => "show", "middleware"=>"message|message"]);
$router->add("/{controller}/{id:\d+}/{action}");
$router->add("/{controller}/{action}");
$router->add("/", ["controller" => "home", "action" => "index"]);

return $router;