<?php

declare(strict_types=1);

namespace Framework;

use ReflectionMethod;
use Framework\Http\Request;

use Framework\Config\Config;
use Framework\Http\Response;
use Framework\Router\Router;
use UnexpectedValueException;
use Framework\Service\Container;
use Framework\View\TemplateViewerInterface;
use Framework\Exceptions\PageNotFoundException;
use Framework\Controller\ControllerRequestHandler;
use Framework\Middleware\MiddlewareRequestHandler;

class App
{
    protected Router $router;
    protected Request $request;
    protected Response $response;
    protected Config $config;
    protected Container $container;
    protected array $middlewareClasses;

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
        $this->initResponse();
        $this->initContainer();
        $this->initMiddlewares();
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

    private function initResponse()
    {
        $this->response = new Response();
    }

    private function initContainer()
    {
        $serviceConfig = $this->config->get("services", []);
        $this->container = Container::getInstance($serviceConfig);
        $this->container->set(Request::class, $this->request);
        $this->container->set(Response::class, $this->response);
        // $firstService1 = $this->container->get("FirstService");
        // $firstService2 = $this->container->get("FirstService");
        // $firstService1->property = "FirstService1 updated";
        // var_dump($firstService1->property);
        // var_dump($firstService2->property);
        // die;
    }

    private function initMiddlewares()
    {
        $middlewares = $this->config->get("middlewares", []);
        $this->middlewareClasses = $middlewares;
    }

    private function initRouter()
    {
        $this->router = new Router($this->config->get("routes", []));
    }

    public function run()
    {
        $this->router->match($this->request);

        if (!$this->request->attributes->has("_controller")) {
            $path = $this->request->getPath();
            $method = $this->request->getMethod();
            throw new PageNotFoundException("No route matched for '$path' with method '$method'");
        }

        $_controller = $this->request->attributes->get("_controller");
        $controller = explode("::", $_controller)[0];
        $action = explode("::", $_controller)[1];
        $params = $this->request->attributes->get("_route_params", []);

        $controllerObject = $this->container->get($controller);
        $controllerObject->setViewer($this->container->get(TemplateViewerInterface::class));
        $controllerObject->setRequest($this->request);
        $controllerObject->setResponse($this->response);
        $args = $this->getActionArguments($controller, $action, $params);

        $middlewares = $this->getMiddlewares($params);
        $controllerRequestHandler = new ControllerRequestHandler(
            $controllerObject,
            $action,
            $args
        );
        $requestHandler = new MiddlewareRequestHandler(
            $middlewares,
            $controllerRequestHandler
        );

        $response = $requestHandler->handle($this->request);
        $response->send();
    }

    private function getMiddlewares(array $params): array
    {
        if (!array_key_exists("middlewares", $params)) {
            return [];
        }

        $middlewares = $params["middlewares"];

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
            if (array_key_exists($name, $params)) {
                $args[$name] = $params[$name];
            } else if ($parameter->getType() !== null) {
                $type = $parameter->getType()->getName();
                $args[$name] = $this->container->get($type);
            } else if ($parameter->isDefaultValueAvailable()) {
                $args[$name] = $parameter->getDefaultValue();
            } else {
                throw new UnexpectedValueException("Missing parameter '$name' for action '$action'");
            }
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
