<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class PhpVersion
{
    public function __construct(
        private string $version,
        private string $lastRelease,
        private DateTimeImmutable $initialRelease,
        private DateTimeImmutable $endOfLife,
        private ?DateTimeImmutable $activeSupportUntil = null,
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getLastRelease(): string
    {
        return $this->lastRelease;
    }

    public function getInitialRelease(): DateTimeImmutable
    {
        return $this->initialRelease;
    }

    public function getActiveSupportUntil(): ?DateTimeImmutable
    {
        return $this->activeSupportUntil;
    }

    public function getEndOfLife(): DateTimeImmutable
    {
        return $this->endOfLife;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'last_release' => $this->lastRelease,
            'initial_release' => $this->initialRelease->format('Y-m-d'),
            'end_of_life' => $this->endOfLife->format('Y-m-d'),
            'active_support_until' => $this->activeSupportUntil ? $this->activeSupportUntil->format('Y-m-d') : null,
        ];
    }
}
