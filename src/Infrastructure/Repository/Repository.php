<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Infrastructure\Database\Connection;
use PDO;

abstract class Repository
{
    protected PDO $pdo;

    public function __construct(Connection $connection)
    {
        $this->pdo = $connection->pdo();
    }
}
