<?php

declare(strict_types=1);

namespace Framework;

use PDO;

class Database
{
    private ?PDO $pdo = null;

    public function __construct(
                                private string $driver,
                                private string $host,
                                private string $name,
                                private string $user,
                                private string $password,
                                private string $charset,
                                private string $port
                                )
                                
    {
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {

            $dsn = "$this->driver:host=$this->host;dbname=$this->name;charset=$this->charset;port=$this->port";

            $this->pdo = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        return $this->pdo;
    }
}
