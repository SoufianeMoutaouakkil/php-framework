<?php

namespace Framework\Database;

interface DatabaseInterface
{
    public function connect(): void;
    public function getConnection(): mixed;
}
