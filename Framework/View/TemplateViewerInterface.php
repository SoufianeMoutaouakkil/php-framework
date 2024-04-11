<?php

namespace Framework\View;

interface TemplateViewerInterface
{
    public function render(string $template, array $data = []): string;    
}