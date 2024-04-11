<?php

declare(strict_types=1);

namespace Framework\Controller;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\RequestHandlerInterface;

class ControllerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private AbstractController $controller,
        private string $action,
        private array $args
    ) {
    }

    public function handle(Request $request): Response
    {
        $this->controller->setRequest($request);

        return ($this->controller)->{$this->action}(...$this->args);
    }
}
