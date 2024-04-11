<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\RequestHandlerInterface;
use Framework\Controller\ControllerRequestHandler;

class MiddlewareRequestHandler implements RequestHandlerInterface
{
    public function __construct(private array $middlewares,
                                private ControllerRequestHandler $controller_handler)
    {
    }

    public function handle(Request $request): Response
    {
        $middleware = array_shift($this->middlewares);
        if ($middleware === null) {
            return $this->controller_handler->handle($request);
        }
        return $middleware->process($request, $this);
    }
}