<?php

declare(strict_types=1);

namespace Framework;

use Framework\Exceptions\PageNotFoundException;
use Framework\Middleware\ControllerRequestHandler;
use Framework\Middleware\MiddlewareRequestHandler;
use Framework\View\MVCTemplateViewer;
use ReflectionMethod;
use UnexpectedValueException;

class Dispatcher
{

    public function __construct(
        private Router $router,
        private Container $container,
        private array $middleware_classes
    ) {}

    public function handle(Request $request): Response
    {

        $path = $this->getPath($request->uri);

        $params = $this->router->match($path, $request->method);
        if (!$params) {
            throw new PageNotFoundException("No route matched for '$path' with method '$request->method'");
        }

        $controller = $this->getControllerName($params);
        $controller_object = $this->getControllerObject($controller, $params);
        $action = $this->getActionName($params);
        $args = $this->getActionArguments($controller, $action, $params);

        $controller_handler = new ControllerRequestHandler($controller_object, $action, $args);
        $middleware_handler = $this->getMiddlewareHandler($params, $controller_handler);

        return $middleware_handler->handle($request);
    }

    private function getControllerObject(string $controller, array $params): object {

        $controller_object = $this->container->get($controller);
        $controller_object->setViewer($this->container->get(MVCTemplateViewer::class));
        $controller_object->setResponse($this->container->get(Response::class));

        return $controller_object;

    }

    private function getMiddlewareHandler(
        array $params, 
        ControllerRequestHandler $controller_handler
    ): MiddlewareRequestHandler
    {
        $middleware = $this->getMiddleware($params);

        array_walk($middleware, function(&$value) {
            $value = $this->container->get($this->middleware_classes[$value]);
        });

        return new MiddlewareRequestHandler($middleware, $controller_handler);
    }

    private function getControllerName(array $params): string
    {

        $controller = $params['controller'];
        $controller = str_replace("-", "", ucwords(strtolower($controller), "-"));


        return $this->getNamespace($params) . "\\" . $controller;
    }

    private function getActionName(array $params): string
    {

        $action = $params['action'];
        $action = lcfirst(str_replace("-", "", ucwords(strtolower($action), "-")));

        return $action;
    }

    private function getActionArguments(string $controller, string $action, array $params): array
    {
        $args = [];

        $method = new ReflectionMethod($controller, $action);

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $args[$name] = $params[$name];
        }

        return $args;
    }

    private function getNamespace(array $params): string
    {
        $namespace = "App\Controllers";
        if (array_key_exists("namespace", $params)) {
            $namespace .= "\\" . $params["namespace"];
        }
        return "App\Controllers";
    }

    private function getPath(string $uri): string
    {
        // Récupération des éléments de la Pretty URL
        $path = parse_url($uri, PHP_URL_PATH);
        if (!$path) {
            throw new UnexpectedValueException("Malformed URL : '{$uri}'");
        }
        return $path;
    }

    public function getMiddleware(array $params): array
    {
        if (! array_key_exists("middleware", $params)) {
            return [];
        }

        $middleware = explode("|", $params["middleware"]);
        return $middleware;
    }
}
