<?php

return [
    App\Services\FirstService::class => [
        "class" => App\Services\FirstService::class,
        "shared" => true
    ],
    Framework\View\TemplateViewerInterface::class => [
        "class" => Framework\View\PHPTemplateViewer::class,
        "shared" => true
    ]
];
