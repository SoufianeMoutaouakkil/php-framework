<?php

declare(strict_types=1);

namespace Framework\Http;

use UnexpectedValueException;

class Request
{
    const METHOD_GET = "get";
    const METHOD_POST = "post";
    const METHOD_PUT = "put";
    const METHOD_DELETE = "delete";
    const METHOD_PATCH = "patch";
    const METHOD_OPTIONS = "options";
    const METHOD_HEAD = "head";

    public Attributes $attributes;
    public function __construct(
        public string $uri,
        public string $method,
        public array $get,
        public array $post,
        public array $files,
        public array $cookie,
        public array $server,
        public string $body,
        public array $headers
    ) {
        $this->method = strtolower($this->method);
        $this->attributes = new Attributes();
    }

    public static function createFromGlobals()
    {
        $body = file_get_contents("php://input");
        $headers = getallheaders();
        return new static(
            $_SERVER["REQUEST_URI"],
            $_SERVER["REQUEST_METHOD"],
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE,
            $_SERVER,
            $body,
            $headers
        );
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        if ($path === false) {
            throw new UnexpectedValueException("Malformed URL: '$this->uri'");
        }
        if ($path === "/") {
            return "/";
        }
        return rtrim(parse_url($this->uri, PHP_URL_PATH), "/");
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function file(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookie[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function hasHeader(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    public function hasGet(string $key): bool
    {
        return isset($this->get[$key]);
    }

    public function hasPost(string $key): bool
    {
        return isset($this->post[$key]);
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]);
    }

    public function hasCookie(string $key): bool
    {
        return isset($this->cookie[$key]);
    }

    public function hasServer(string $key): bool
    {
        return isset($this->server[$key]);
    }

    public function isGet(): bool
    {
        return $this->method === Request::METHOD_GET;
    }

    public function isPost(): bool
    {
        return $this->method === Request::METHOD_POST;
    }

    public function isPut(): bool
    {
        return $this->method === Request::METHOD_PUT;
    }

    public function isDelete(): bool
    {
        return $this->method === Request::METHOD_DELETE;
    }

    public function isPatch(): bool
    {
        return $this->method === Request::METHOD_PATCH;
    }

    public function isOptions(): bool
    {
        return $this->method === Request::METHOD_OPTIONS;
    }

    public function isHead(): bool
    {
        return $this->method === Request::METHOD_HEAD;
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->server("HTTP_X_REQUESTED_WITH") === "XMLHttpRequest";
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
