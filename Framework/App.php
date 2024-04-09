<?php

declare(strict_types=1);

namespace Framework;

use Exception;
use ReflectionMethod;
use Framework\Http\Request;
use Framework\Config\Config;
use Framework\Http\Response;
use Framework\Router\Router;
use UnexpectedValueException;
use Framework\Exceptions\PageNotFoundException;

class App
{
    private Router $router;
    private Request $request;
    private Response $response;
    private Config $config;

    private Container $container;

    public function __construct()
    {
        if (!defined("ROOT_PATH")) {
            define("ROOT_PATH", dirname(__DIR__));
        }
        if (!defined("CONFIG_PATH")) {
            define("CONFIG_PATH", dirname(__DIR__) . "/config");
        }

        $this->init();
    }

    public function init()
    {
        $this->initAutoload();
        $this->initConfig();
        $this->initErrorHandler();
        $this->initRequest();
        $this->initRouter();
    }


    private function initAutoload()
    {
        spl_autoload_register(function (string $class_name) {
            $namespace = explode("\\", $class_name)[0];

            $class = $class_name;
            if ($namespace === "App") {
                $class = str_replace("App\\", "src/", $class_name);
                $class = str_replace("\\", "/", $class);
            }

            $classPath = ROOT_PATH . "/$class.php";
            $classPath = str_replace("\\", "/", $classPath);
            if (file_exists($classPath)) {
                require_once $classPath;
            }
        });
    }

    private function initErrorHandler()
    {
        set_error_handler("Framework\Error\ErrorHandler::handleError");
        set_exception_handler("Framework\Error\ErrorHandler::handleException");
    }

    private function initConfig()
    {
        $this->config = Config::getInstance();
        $this->config->init();
    }

    private function initRequest()
    {
        $this->request = Request::createFromGlobals();
    }

    private function initRouter()
    {
        $this->router = new Router($this->config->get("routes", []));
    }

    public function run()
    {
        $this->router->match($this->request);

        var_dump($this->request);die;

        if ($params === false) {

            throw new PageNotFoundException("No route matched for '$path' with method '{$request->method}'");
        }

        $action = $this->getActionName($params);
        $controller = $this->getControllerName($params);

        $controller_object = $this->container->get($controller);

        $controller_object->setViewer($this->container->get(TemplateViewerInterface::class));

        $controller_object->setResponse($this->container->get(Response::class));

        $args = $this->getActionArguments($controller, $action, $params);

        $controller_handler = new ControllerRequestHandler(
            $controller_object,
            $action,
            $args
        );

        $middleware = $this->getMiddleware($params);

        $middleware_handler = new MiddlewareRequestHandler(
            $middleware,
            $controller_handler
        );

        return $middleware_handler->handle($request);
    }

    private function getMiddleware(array $params): array
    {
        if (!array_key_exists("middleware", $params)) {

            return [];
        }

        $middlewares = explode("|", $params["middlewares"]);

        array_walk($middlewares, function (&$middleware) {

            if (!array_key_exists($middleware, $this->middlewareClasses)) {

                throw new UnexpectedValueException("Middleware '$middleware' not found in config settings");
            }

            $middleware = $this->container->get($this->middlewareClasses[$middleware]);
        });

        return $middlewares;
    }

    private function getActionArguments(string $controller, string $action, array $params): array
    {
        $args = [];

        $method = new ReflectionMethod($controller, $action);

        foreach ($method->getParameters() as $parameter) {

            $name = $parameter->getName();

            $args[$name] = $params[$name];
        }

        return $args;
    }

    private function getControllerName(array $params): string
    {
        $controller = $params["controller"];

        $controller = str_replace("-", "", ucwords(strtolower($controller), "-"));

        $namespace = "App\Controllers";

        if (array_key_exists("namespace", $params)) {

            $namespace .= "\\" . $params["namespace"];
        }

        return $namespace . "\\" . $controller;
    }

    private function getActionName(array $params): string
    {
        $action = $params["action"];

        $action = lcfirst(str_replace("-", "", ucwords(strtolower($action), "-")));

        return $action;
    }

    private function getPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === false) {

            throw new UnexpectedValueException("Malformed URL: '$uri'");
        }

        return $path;
    }
}
