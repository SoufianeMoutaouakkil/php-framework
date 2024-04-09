<?php

declare(strict_types=1);

namespace Framework\Error;

use ErrorException;
use Throwable;
use Framework\Exceptions\PageNotFoundException;

class ErrorHandlerException extends ErrorException
{
}

class ErrorHandler
{
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool
    {
        throw new ErrorHandlerException($errstr, 0, $errno, $errfile, $errline);
    }

    public static function handleException(Throwable $exception): void
    {
        if ($exception instanceof PageNotFoundException) {
    
            http_response_code(404);
    
            $template = "404.php";
    
        } else {
        
            http_response_code(500);
    
            $template = "500.php";
    
        }
    
        if (isset($_ENV["SHOW_ERRORS"]) && $_ENV["SHOW_ERRORS"]) {
            die($exception->getMessage());
            ini_set("display_errors", "1");
        } else {
            die("An error occurred. Please try again later.");
    
            ini_set("display_errors", "0");
    
            ini_set("log_errors", "1");

            require_once dirname(__DIR__, 2) . "/views/$template";
    
        }
    
        throw $exception;
    }
}
