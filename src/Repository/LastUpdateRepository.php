<?php

declare(strict_types=1);

namespace App\Repository;

use App\Clock;
use DateTimeImmutable;
use PDO;
use PDOException;
use RuntimeException;

final class LastUpdateRepository
{
    public function __construct(
        private PDO $db,
        private Clock $clock,
    ) {
    }

    public function get(): DateTimeImmutable
    {
        $stmt = $this->db->query('SELECT * FROM last_update;');
        if (false === ($date = $stmt->fetchColumn())) {
            throw new RuntimeException('Data has never been updated.');
        }

        return new DateTimeImmutable($date);
    }

    public function save(): void
    {
        $this->db->beginTransaction();

        try {
            $this->db->exec('DELETE FROM last_update');

            $stmt = $this->db->prepare('INSERT INTO last_update VALUES (:date)');
            $stmt->bindValue('date', $this->clock->now()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
        }
    }
}
