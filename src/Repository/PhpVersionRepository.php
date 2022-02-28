<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PhpVersion;

interface PhpVersionRepository
{
    /**
     * @return PhpVersion[]
     */
    public function all(): iterable;

    /**
     * @return PhpVersion[]
     */
    public function maintenedVersions(): iterable;

    /**
     * @return PhpVersion[]
     */
    public function unmaintenedVersions(): iterable;

    public function save(PhpVersion ...$versions): void;
}
