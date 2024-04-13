<?php

return [
    App\Services\FirstService::class => [
        "class" => App\Services\FirstService::class,
        "shared" => true
    ],
    Framework\View\TemplateViewerInterface::class => [
        "class" => Framework\View\PHPTemplateViewer::class,
        "shared" => true
    ],
    Framework\Database\DatabaseInterface::class => [
        "class" => Framework\Database\PdoDatabase::class,
        "shared" => true,
        "arguments" => [
            "dsn" => "%env(DB_DSN)",
            "dbname" => "%env(DB_NAME)",
            "username" => "%env(DB_USER)",
            "password" => "%env(DB_PASS)",
        ]
    ],
];
