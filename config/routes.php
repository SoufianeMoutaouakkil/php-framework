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
];
