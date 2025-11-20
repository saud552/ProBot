<?php

declare(strict_types=1);

namespace Numbers\Database;

use PDO;
use PDOException;

class Connection
{
    private PDO $pdo;

    public function __construct(string $path)
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec('PRAGMA journal_mode = WAL;');
        $this->pdo->exec('PRAGMA synchronous = NORMAL;');
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
