<?php

declare(strict_types=1);

namespace Framework\Database;

use PDO;

class PdoDatabase implements DatabaseInterface
{
    private ?PDO $pdo = null;

    public function __construct(
        private string $dsn,
        private string $dbname,
        private string $username,
        private string $password,
    ) {
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $dsn = $this->dsn . ';dbname=' . $this->dbname;
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        return $this->pdo;
    }

    public function connect(): void
    {
        $this->getConnection();
    }
}
