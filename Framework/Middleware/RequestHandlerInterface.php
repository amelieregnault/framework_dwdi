<?php

namespace Framework\Middleware;

use Framework\Request;
use Framework\Response;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}