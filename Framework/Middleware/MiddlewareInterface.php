<?php

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\RequestHandlerInterface;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response;    
}