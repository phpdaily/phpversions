<?php

declare(strict_types=1);

namespace App;

use PDO;

final class PDOFactory
{
    public function __construct(
        private string $dsn,
        private ?string $username = null,
        private ?string $password = null,
    ) {
    }

    public function create(): PDO
    {
        $db = new PDO($this->dsn, $this->username, $this->password);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $db;
    }
}
