<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PhpVersion;
use DateTimeImmutable;
use PDO;
use PDOException;

final class PhpVersionRepository
{
    public function __construct(
        private PDO $db,
    ) {
    }

    /**
     * @return PhpVersion[]
     */
    public function all(): iterable
    {
        $stmt = $this->db->query('SELECT * FROM php_version ORDER BY end_of_life_date DESC');
        while ($row = $stmt->fetch()) {
            yield $this->createObjectInstance($row);
        }
    }

    /**
     * @return PhpVersion[]
     */
    public function maintenedVersions(): iterable
    {
        $stmt = $this->db->query('SELECT * FROM php_version WHERE end_of_life_date > DATE("now") ORDER BY end_of_life_date DESC');
        while ($row = $stmt->fetch()) {
            yield $this->createObjectInstance($row);
        }
    }

    /**
     * @return PhpVersion[]
     */
    public function unmaintenedVersions(): iterable
    {
        $stmt = $this->db->query('SELECT * FROM php_version WHERE end_of_life_date <= DATE("now") ORDER BY end_of_life_date DESC');
        while ($row = $stmt->fetch()) {
            yield $this->createObjectInstance($row);
        }
    }

    public function save(PhpVersion ...$versions): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($versions as $phpVersion) {
                $stmt = $this->db->prepare('INSERT OR REPLACE INTO php_version(version, last_release, initial_release_date, end_of_life_date, active_support_until) VALUES(:version, :last_release, :initial_release, :end_of_life_date, :active_support_until)');
                $stmt->bindValue('version', $phpVersion->getVersion());
                $stmt->bindValue('last_release', $phpVersion->getLastRelease());
                $stmt->bindValue('initial_release', $phpVersion->getInitialRelease()->format('Y-m-d'));
                $stmt->bindValue('active_support_until', $phpVersion->getActiveSupportUntil()?->format('Y-m-d'));
                $stmt->bindValue('end_of_life_date', $phpVersion->getEndOfLife()->format('Y-m-d'));
                $stmt->execute();
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
        }
    }

    private function createObjectInstance(array $data): PhpVersion
    {
        return new PhpVersion(
            version: $data['version'],
            lastRelease: $data['last_release'],
            initialRelease: DateTimeImmutable::createFromFormat('Y-m-d', $data['initial_release_date']),
            endOfLife: DateTimeImmutable::createFromFormat('Y-m-d', $data['end_of_life_date']),
            activeSupportUntil: $data['active_support_until'] ? DateTimeImmutable::createFromFormat('Y-m-d', $data['active_support_until']) : null,
        );
    }
}
