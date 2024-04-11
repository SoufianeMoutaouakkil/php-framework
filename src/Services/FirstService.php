<?php

namespace App\Services;

class FirstService
{
    public $property;

    public function __construct()
    {
        $this->property = uniqid();
    }
}