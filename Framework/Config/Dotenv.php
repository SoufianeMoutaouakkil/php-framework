<?php

declare(strict_types=1);

namespace Framework\Config;

class Dotenv
{
    public static function load(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {

            list($name, $value) = explode("=", $line, 2);

            $_ENV[$name] = $value;
        }
    }
}
