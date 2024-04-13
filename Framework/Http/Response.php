<?php

declare(strict_types=1);

namespace Framework\Http;

class Response
{
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_NO_CONTENT = 204;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    private string $body = "";

    private array $headers = [];

    private int $statusCode = 0;

    public function setStatusCode(int $code): void
    {
        $this->statusCode = $code;
    }

    public function redirect(string $url): void
    {
        $this->addHeader("Location", $url);
    }

    public function addHeader(string $header, mixed $value, bool $replace = true): void
    {
        if ($replace || !isset($this->headers[$header])) {
            $this->headers[$header] = $value;
        }
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function appendBody(string $body): void
    {
        $this->body .= $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function json(array $data): void
    {
        $this->addHeader("Content-Type", "application/json");
        $this->setBody(json_encode($data));
    }

    public function sendFile(string $file): void
    {
        $this->addHeader("Content-Type", mime_content_type($file));
        $this->addHeader("Content-Disposition", "attachment; filename=" . basename($file));
        $this->setBody(file_get_contents($file));
    }

    public function send(): void
    {
        $this->setStatusCodeHeader();
        $this->setHeaders();
        echo $this->body;
    }

    private function setHeaders(): void
    {
        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
    }

    private function setStatusCodeHeader(): void
    {
        if ($this->statusCode) {
            http_response_code($this->statusCode);
        }
    }
}
