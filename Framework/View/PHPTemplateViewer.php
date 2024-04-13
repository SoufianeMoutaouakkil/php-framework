<?php

declare(strict_types=1);

namespace Framework\View;

class PHPTemplateViewer implements TemplateViewerInterface
{
    public function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();

        require_once ROOT_PATH . "/src/Views/" . $template . ".php";

        return ob_get_clean();
    }
}
