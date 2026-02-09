<?php

namespace App\DB;

use PDO;

class Sql extends PDO
{
    public function __construct()
    {
        parent::__construct(
            "mysql:host=127.0.0.1;dbname=db_ecommerce;charset=utf8",
            "root",
            "a",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    public function select(string $query, array $params = []): array
    {
        $stmt = $this->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function execute(string $query, array $params = []): void
    {
        $stmt = $this->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
    }
}
