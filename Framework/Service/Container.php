<?php

declare(strict_types=1);

namespace Framework\Service;

use Exception;
use ReflectionClass;

class Container
{
    private $config;
    private $singletons = [];
    private $stack = [];
    static private $instance = null;
    const TYPE_SERVICE = "service";
    const TYPE_CONTROLLER = "controller";
    const TYPE_MIDDLEWARE = "middleware";
    const TYPE_MODEL = "model";
    const TYPE_ENTITY = "entity";

    protected function __construct(array $config)
    {
        $this->config = $config;
        $this->servicesAutodiscovery();
        $this->controllersAutodiscovery();
        $this->middlewaresAutodiscovery();
        $this->modelsAutodiscovery();
        $this->entitiesAutodiscovery();
        // var_dump($this->config);die;
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function get($serviceName, $parentClassName = null)
    {
        if (isset($this->singletons[$serviceName])) {
            return $this->singletons[$serviceName];
        }

        if (!isset($this->config[$serviceName])) {
            throw new Exception("Class '$serviceName' not defined in container");
        }

        $definition = $this->config[$serviceName];

        $instance = $this->createService($serviceName, $parentClassName);
        if (isset($definition['shared']) && $definition['shared'] === true) {
            $this->singletons[$serviceName] = $instance;
        }

        return $instance;
    }

    public function set(string $className, object $instance)
    {
        $this->singletons[$className] = $instance;
    }

    private function servicesAutodiscovery(bool $force = false): void
    {
        $servicesPath = $this->getServicesPath();
        if (is_dir($servicesPath)) {
            $this->addFolder($servicesPath, self::TYPE_SERVICE, $force);
        }
    }

    private function middlewaresAutodiscovery()
    {
        $middlewaresPath = $this->getMiddlewaresPath();
        if (is_dir($middlewaresPath)) {
            $this->addFolder($middlewaresPath, self::TYPE_MIDDLEWARE);
        }
    }

    private function controllersAutodiscovery(bool $force = false): void
    {
        $controllersPath = $this->getControllersPath();
        if (is_dir($controllersPath)) {
            $this->addFolder($controllersPath, self::TYPE_CONTROLLER, $force);
        }
    }

    private function modelsAutodiscovery(bool $force = false): void
    {
        $modelsPath = $this->getModelsPath();
        if (is_dir($modelsPath)) {
            $this->addFolder($modelsPath, self::TYPE_MODEL, $force);
        }
    }

    private function entitiesAutodiscovery(bool $force = false): void
    {
        $entitiesPath = $this->getEntitiesPath();
        if (is_dir($entitiesPath)) {
            $this->addFolder($entitiesPath, self::TYPE_ENTITY, $force);
        }
    }

    private function addFolder(string $path, string $type, bool $force = false): void
    {
        $files = scandir($path);
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            $filePath = "$path/$file";
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_dir($filePath)) {
                $this->addFolder($filePath, $type, $force);
                continue;
            } else {
                $this->addFile($filePath, $type, $force);
            }
        }
    }

    private function addFile(string $file, $type, bool $force = false): void
    {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            return;
        }
        $fullClassName = $this->getFullClassName($file, $type);
        if (isset($this->config[$fullClassName]) && !$force) {
            return;
        }
        $this->config[$fullClassName] = ['class' => $fullClassName];
    }

    private function getFullClassName(string $file, $type)
    {
        list($baseNamespace, $path) = $this->getTypeData($type);
        $file = str_replace($path, '', $file);
        $file = str_replace('/', '\\', $file);
        $file = str_replace('.php', '', $file);
        return $baseNamespace . $file;
    }

    private function createService($serviceName, $parentClassName = null)
    {
        $definition = $this->config[$serviceName];
        $className = $definition['class'];
        if (in_array($className, $this->stack)) {
            throw new Exception("Circular dependency detected for class '$className' with parent '$parentClassName'");
        } else {
            $this->stack[] = $className;
        }

        $reflection = new ReflectionClass($className);
        $arguments = [];
        if (isset($definition['arguments'])) {
            foreach ($definition['arguments'] as $argument) {
                if (is_callable($argument)) {
                    // Lazy loading with callable
                    $arguments[] = call_user_func($argument, $this);
                } else if (strpos($argument, '%env(') === 0) {
                    // Extract environment variable name (remove '%env(' and ')')
                    $envVarName = substr($argument, 5, -1);
                    $arguments[] = $this->getEnv($envVarName);
                } else if (strpos($argument, '@') === 0) {
                    $dependencyName = substr($argument, 1);
                    $arguments[] = $this->get($dependencyName, $className);
                } else {
                    // Other types of arguments
                    $arguments[] = $argument;
                }
            }
        } else {
            $constructor = $reflection->getConstructor();
            if ($constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    $arguments[] = $this->get($parameter->getType()->getName(), $className);
                }
            }
        }

        $this->stack = array_diff($this->stack, [$className]);
        return $reflection->newInstanceArgs($arguments);
    }

    private function getEnv($name, $default = null)
    {
        return isset($_ENV[$name]) ? $_ENV[$name] : $default;
    }

    private function getControllersPath()
    {
        return str_replace('\\', '/', ROOT_PATH . "/src/Controllers");
    }

    private function getServicesPath()
    {
        return str_replace('\\', '/', ROOT_PATH . "/src/Services");
    }

    private function getMiddlewaresPath()
    {
        return str_replace('\\', '/', ROOT_PATH . "/src/Middlewares");
    }

    private function getModelsPath()
    {
        return str_replace('\\', '/', ROOT_PATH . "/src/Models");
    }
    private function getEntitiesPath()
    {
        return str_replace('\\', '/', ROOT_PATH . "/src/Entities");
    }

    private function getTypeData($type)
    {
        switch ($type) {
            case self::TYPE_SERVICE:
                return ['App\\Services', $this->getServicesPath()];
            case self::TYPE_CONTROLLER:
                return ['App\\Controllers', $this->getControllersPath()];
            case self::TYPE_MIDDLEWARE:
                return ['App\\Middlewares', $this->getMiddlewaresPath()];
            case self::TYPE_MODEL:
                return ['App\\Models', $this->getModelsPath()];
            case self::TYPE_ENTITY:
                return ['App\\Entities', $this->getEntitiesPath()];
            default:
                throw new Exception("Invalid service type '$type'");
        }
    }
}
