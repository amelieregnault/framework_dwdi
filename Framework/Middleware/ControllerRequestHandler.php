<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Controller;
use Framework\Request;
use Framework\Response;

class ControllerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private Controller $controller,
        private string $action,
        private array $args
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->controller->setRequest($request);
        return ($this->controller)->{$this->action}(...$this->args);
    }
}