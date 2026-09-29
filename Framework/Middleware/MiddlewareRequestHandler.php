<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\MiddleWare\ControllerRequestHandler;
use Framework\Request;
use Framework\Response;

use Override;

class MiddlewareRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private array $middlewares,       
        private ControllerRequestHandler $controller_handler
    )
    {    
    }

    #[Override]
    public function handle(Request $request): Response
    {
        $middleware = array_shift($this->middlewares);

        if ($middleware === null) {
            return $this->controller_handler->handle($request);
        }
        return $middleware->process($request, $this);
    }
}