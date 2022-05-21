<?php

declare(strict_types=1);

namespace App\Storage;

use App\Model\PhpRelease;
use App\Model\PhpVersion;
use App\Repository\PDO\LastUpdateRepository;
use App\Repository\PDO\PdoPhpReleaseRepository;
use App\Repository\PDO\PdoPhpVersionRepository;
use App\Storage;

final class PDOStorage implements Storage
{
    public function __construct(
        private LastUpdateRepository $lastUpdateRepository,
        private PdoPhpReleaseRepository $releaseRepository,
        private PdoPhpVersionRepository $versionRepository
    ) {
    }

    /**
     * @param iterable<PhpVersion> $versions
     * @param iterable<PhpRelease> $releases
     */
    public function write(iterable $versions, iterable $releases): void
    {
        $this->versionRepository->save(...$versions);
        $this->releaseRepository->save(...$releases);

        $this->lastUpdateRepository->save();
    }
}
