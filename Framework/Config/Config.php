<?php

namespace Framework\Config;

use Exception;

class ConfigException extends Exception
{
}
class Config
{

    private $configPath = "";
    private $config = [];
    private $isInitilized = false;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public function init()
    {
        if ($this->isInitilized === false) {
            $this->setConfigPath();
            $this->loadConfig();
            $this->loadDotenvConfig();
            $this->loadDatabaseConfig();
            $this->loadRoutesConfig();
            $this->loadServicesConfig();
            $this->loadMiddlewaresConfig();
        }
    }

    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }
        $keys = explode(".", $key);
        $value = $this->config[$keys[0]];
        foreach ($keys as $i => $key) {
            if ($i !== 0) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return $default;
                }
            }
        }

        return $value;
    }

    private function setConfigPath()
    {
        if (defined("CONFIG_PATH")) {
            $this->configPath = CONFIG_PATH;
        } elseif (defined("ROOT_PATH")) {
            $this->configPath = ROOT_PATH . "/config";
        }
        $this->configPath = ROOT_PATH . "/config";
    }

    private function loadMiddlewaresConfig()
    {
        $middlewaresFilePath = $this->configPath . "/middlewares.php";
        if (file_exists($middlewaresFilePath)) {
            $middlewares = require_once $middlewaresFilePath;
            $this->setConfig($middlewares, "middlewares");
        }
    }

    private function loadConfig()
    {
        $configFilePath = $this->configPath . "/config.php";
        if (file_exists($configFilePath)) {
            $config = require_once $configFilePath;
            $this->setConfig($config);
        }
    }

    private function loadDotenvConfig()
    {
        $dotenvFilePath = $this->configPath . "/.env";
        if (file_exists($dotenvFilePath)) {
            Dotenv::load($dotenvFilePath);
        }
    }

    private function loadDatabaseConfig()
    {
        $databaseConfigFilePath = $this->configPath . "/database.php";
        if (file_exists($databaseConfigFilePath)) {
            $databaseConfig = require_once $databaseConfigFilePath;
            $this->setConfig($databaseConfig, "database");
        }
    }

    private function loadServicesConfig()
    {
        $servicesConfigFilePath = $this->configPath . "/services.php";
        if (file_exists($servicesConfigFilePath)) {
            $servicesConfig = require_once $servicesConfigFilePath;
            $this->setConfig($servicesConfig, "services");
        }
    }

    private function loadRoutesConfig()
    {
        $routesConfigFilePath = $this->configPath . "/routes.php";
        if (file_exists($routesConfigFilePath)) {
            $routesConfig = require_once $routesConfigFilePath;
            $this->setConfig($routesConfig, "routes");
        }
    }

    private function setConfig($config, $key = null)
    {
        if (is_null($key)) {
            if (is_array($config)) {
                self::$config = [
                    ...self::$config,
                    ...$config
                ];
            } else {
                throw new ConfigException("Config withou key must be an array");
            }
        } else {
            self::$config[$key] = $config;
        }
    }
}
