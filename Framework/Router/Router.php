<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Http\Request;
use UnexpectedValueException;

class Router
{
    public function __construct(private array $routes = [])
    {
    }

    public function add(string $name, string $path, string $controller, array|string $methods = [], array $options = []): void
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }
        if (array_key_exists($name, $this->routes)) {
            throw new UnexpectedValueException("Route $name already exists");
        } else {
            $this->routes[$name] = [
                "path" => $path,
                "controller" => $controller,
                "methods" => $methods,
                "options" => $options
            ];
        }
    }

    public function get(string $name, string $path, string $controller, array $options = []): void
    {
        $this->add($name, $path, $controller, "GET", $options);
    }

    public function post(
        string $name,
        string $path,
        string $controller,
        array $options = []
    ): void {
        $this->add($name, $path, $controller, "POST", $options);
    }

    public function put(
        string $name,
        string $path,
        string $controller,
        array $options = []
    ): void {
        $this->add($name, $path, $controller, "PUT", $options);
    }

    public function delete(
        string $name,
        string $path,
        string $controller,
        array $options = []
    ): void {
        $this->add($name, $path, $controller, "DELETE", $options);
    }

    public function getRoute(string $name): array
    {
        if (!array_key_exists($name, $this->routes)) {
            throw new UnexpectedValueException("Route $name not found");
        } else {
            return $this->routes[$name];
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(
        Request $request
    ): void {
        $path = $request->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $routeName => $route) {
            // validate method
            if (!empty($route["methods"])) {
                $methods = array_filter(
                    $route["methods"],
                    function ($m) use ($method) {
                        return strtolower($m) === strtolower($method);
                    }
                );
                if (empty($methods)) {
                    continue;
                }
            }

           $pattern = $this->getPatternFromRoutePath($route["path"]);
            // if ($routeName === "home") {
            //     var_dump($pattern);
            //     var_dump($path);
            //     var_dump($method);
            //     die;
            // }
            if (preg_match($pattern, $path, $matches)) {

                $matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
                if (!empty($route["options"])) {
                    $options = array_merge($matches, $route["options"]);
                } else {
                    $options = $matches;
                }
                $request->attributes->set("_route", $routeName);
                $request->attributes->set("_route_params", $options);
                $request->attributes->set("_controller", $route["controller"]);
            }
        }
    }

    private function getPatternFromRoutePath(string $routePath): string
    {

        $segments = explode("/", trim($routePath));

        $segments = array_map(function (string $segment): string {

            if (preg_match("#^\{([a-z][a-z0-9]*)\}$#", $segment, $matches)) {

                return "(?<" . $matches[1] . ">[^/]*)";
            }

            if (preg_match("#^\{([a-z][a-z0-9]*):(.+)\}$#", $segment, $matches)) {

                return "(?<" . $matches[1] . ">" . $matches[2] . ")";
            }

            return $segment;
        }, $segments);

        return "#^" . implode("/", $segments) . "$#iu";
    }
}
