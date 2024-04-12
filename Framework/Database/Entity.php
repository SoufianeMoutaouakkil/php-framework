<?php

namespace Framework\Database;

abstract class Entity
{
    protected $_errors = [];

    public function __construct($data = [])
    {
        if ($data) {
            $this->fitData($data);
        }
    }

    public function fitData($data)
    {
        foreach ($data as $key => $value) {
            $key = $this->getPropertyName($key);
            if (property_exists($this, $key)) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                } else {
                    $this->$key = $value;
                }
            }
        }
    }

    public function toArray()
    {
        $data = [];
        $properties = $this->getProperties();
        foreach ($properties as $property) {
            $field = $this->getFieldName($property);
            $getter = 'get' . ucfirst($property);
            if (method_exists($this, $getter)) {
                $propValue = $this->$getter();
            } else {
                $propValue = $this->$property;
            }
            if (is_null($propValue)) {
                continue;
            }
            $data[$field] = $propValue;
        }
        return $data;
    }

    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->$name = $value;
        }
    }

    public function __call($name, $arguments)
    {
        $property = lcfirst(substr($name, 3));
        if (strpos($name, 'get') === 0) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
        } elseif (strpos($name, 'set') === 0) {
            if (property_exists($this, $property)) {
                $this->$property = $arguments[0];
            }
        } else {
            throw new \Exception("Method $name not found");
        }
    }

    public function validate()
    {
        $this->_errors = [];
        $this->validateEntity();
    }

    abstract protected function validateEntity(): void;

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return count($this->_errors) > 0;
    }

    public function addError($field, $message)
    {
        $this->_errors[$field] = $message;
    }

    public function getError($field)
    {
        return $this->_errors[$field] ?? null;
    }

    public function hasError($field)
    {
        return isset($this->_errors[$field]);
    }

    private function getProperties()
    {
        $properties = [];
        foreach ($this as $key => $value) {
            if (strpos($key, '_') !== 0) {
                $properties[] = $key;
            }
        }
        return $properties;
    }

    private function getPropertyName($field)
    {
        $parts = explode('_', $field);
        $name = '';
        foreach ($parts as $part) {
            $name .= ucfirst($part);
        }
        return lcfirst($name);
    }

    private function getFieldName($property)
    {
        $name = '';
        $parts = preg_split('/(?=[A-Z])/', $property);
        foreach ($parts as $part) {
            $name .= strtolower($part) . '_';
        }
        return rtrim($name, '_');
    }
}
