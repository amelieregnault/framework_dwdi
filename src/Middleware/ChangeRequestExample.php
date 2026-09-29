<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandlerInterface;
use Framework\Request;
use Framework\Response;

class ChangeRequestExample implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $request->post = array_map("trim", $request->post);
        $response = $next->handle($request);
        return $response;
    }      
}