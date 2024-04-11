<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\RequestHandlerInterface;
use Framework\Middleware\MiddlewareInterface;

class ChangeResponseExample implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $response = $next->handle($request);
        $response->setBody($response->getBody() . " hello from the middleware");
        return $response;
    }
}
