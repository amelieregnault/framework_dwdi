<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\MiddlewareInterface;
use Framework\Request;
use Framework\RequestHandlerInterface;
use Framework\Response;
use Override;

class RedirectExemple implements MiddlewareInterface
{

    public function __construct(private Response $response)
    {
    }

    #[Override]
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $this->response->redirect("/product/index");
        return $this->response;
    }

}