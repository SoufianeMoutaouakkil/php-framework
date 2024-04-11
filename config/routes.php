<?php

return [
    "create_user" => [
        "path" => "/users",
        "controller" => "App\Controllers\UserController::create",
        "methods" => ["POST", "GET"], // optional
        "options" => [
            "middleware" => ["auth"]
        ]
    ],
    "get_user" => [
        "path" => "/users/{id}",
        "controller" => "App\Controllers\UserController::get",
        "options" => [
            "middlewares" => ["example"]
        ]
    ],
    "home" => [
        "path" => "/",
        "controller" => "App\Controllers\HomeController::index"
    ]
];
