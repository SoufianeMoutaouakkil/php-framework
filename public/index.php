<?php

declare(strict_types=1);

use Framework\App;

define("ROOT_PATH", dirname(__DIR__));
define("CONFIG_PATH", ROOT_PATH . "/config");

require ROOT_PATH . "/Framework/App.php";

$app = new App();
$app->run();
