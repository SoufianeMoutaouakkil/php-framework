<?php

use Framework\App;
use Framework\Config\Dotenv;

declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__));
define("CONFIG_PATH", ROOT_PATH . "/config");

spl_autoload_register(function (string $class_name) {

    require_once ROOT_PATH . "/src/" . str_replace("\\", "/", $class_name) . ".php";

});

set_error_handler("Framework\ErrorHandler::handleError");

set_exception_handler("Framework\ErrorHandler::handleException");

$router = require_once ROOT_PATH . "/config/routes.php";

$container = require_once ROOT_PATH . "/config/services.php";

$middleware = require_once ROOT_PATH . "/config/middleware.php";

$app = new App();
$app->run();
