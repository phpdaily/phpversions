<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\PhpRelease;

interface PhpReleaseRepository
{
    /**
     * @return PhpRelease[]
     */
    public function all(): iterable;

    public function save(PhpRelease ...$releases): void;
}
