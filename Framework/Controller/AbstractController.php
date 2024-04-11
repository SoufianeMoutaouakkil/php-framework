<?php

declare(strict_types=1);

namespace Framework\Controller;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\View\TemplateViewerInterface;

abstract class AbstractController
{
    protected Request $request;

    protected Response $response;

    protected TemplateViewerInterface $viewer;

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setViewer(TemplateViewerInterface $viewer): void
    {
        $this->viewer = $viewer;
    }

    protected function render(string $template, array $data = []): Response
    {
        $this->response->setBody($this->viewer->render($template, $data));

        return $this->response;
    }

    protected function redirect(string $url): Response
    {
        $this->response->redirect($url);

        return $this->response;
    }

    protected function json(array $data): Response
    {
        $this->response->json($data);

        return $this->response;
    }
}
