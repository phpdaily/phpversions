<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;

final class PhpRelease
{
    public static function fromArray(array $data): self
    {
        return new self(
            $data['version'],
            DateTimeImmutable::createFromFormat('Y-m-d', $data['release_date']),
        );
    }

    public function __construct(
        public string $version,
        public DateTimeImmutable $releaseDate,
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'release_date' => $this->releaseDate->format('Y-m-d'),
        ];
    }
}
