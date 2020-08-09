<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PhpRelease;
use PDO;
use PDOException;

final class PhpReleaseRepository
{
    public function __construct(
        private PDO $db,
    ) {
    }

    /**
     * @return PhpRelease[]
     */
    public function all(): iterable
    {
        $stmt = $this->db->query('SELECT * FROM php_release ORDER BY release_date DESC');
        while ($row = $stmt->fetch()) {
            yield PhpRelease::fromArray($row);
        }
    }

    public function save(PhpRelease ...$releases): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($releases as $release) {
                $stmt = $this->db->prepare('INSERT OR REPLACE INTO php_release(version, release_date) VALUES (:version, :date)');
                $stmt->bindValue('version', $release->getVersion());
                $stmt->bindValue('date', $release->getReleaseDate()->format('Y-m-d'));
                $stmt->execute();
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
        }
    }
}
