<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandlerInterface;
use Framework\Request;
use Framework\Response;

class ChangeResponseExample implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $response = $next->handle($request);
        $response->setBody($response->getBody(). " hello from the middleware");
        return $response;
    }
}