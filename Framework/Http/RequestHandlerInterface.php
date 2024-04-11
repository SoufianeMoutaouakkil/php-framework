<?php

namespace Framework\Http;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}
