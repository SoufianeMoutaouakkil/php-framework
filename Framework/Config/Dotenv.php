<?php

declare(strict_types=1);

namespace Framework\Config;

class Dotenv
{
    public static function load(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            if (strpos($line, "#") === 0) {
                continue;
            } else if (strpos($line, "=") === false) {
                continue;
            }
            list($key, $value) = explode("=", $line, 2);
            $_ENV[$key] = $value;
        }
    }
}
