<?php

declare(strict_types=1);

namespace App\Storage;

use App\Clock;
use App\Model\PhpVersion;
use App\Storage;
use JDecool\Collection\Collection;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

final class FilesystemStorage implements Storage
{
    public function __construct(
        private Clock $clock,
        private SerializerInterface $serializer,
        private string $outputFolder = '',
    ) {
        if (trim($this->outputFolder) === '') {
            $this->outputFolder = getcwd();
        }
    }

    public function write(iterable $versions, iterable $releases): void
    {
        $this->writeAllVersionsFile($versions);
        $this->writeMaintenedVersions($versions);
        $this->writeUnmaintenedVersions($versions);
        $this->writeReleaseVersions($releases);
    }

    private function writeAllVersionsFile(iterable $versions): void
    {
        $this->writeFile('all.json', $versions);
    }

    /**
     * @param iterable<PhpVersion> $versions
     */
    private function writeMaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))->filter(
            fn (PhpVersion $version): bool => $version->getEndOfLife() > $this->clock->now(),
        );

        $this->writeFile('maintened.json', $data);
    }

    private function writeUnmaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))->filter(
            fn (PhpVersion $version): bool => $version->getEndOfLife() <= $this->clock->now(),
        );

        $this->writeFile('unmaintened.json', $data);
    }

    private function writeReleaseVersions(iterable $releases): void
    {
        $this->writeFile('releases.json', $releases);
    }

    private function writeFile(string $path, iterable $data): void
    {
        $fullpath = rtrim($this->outputFolder, DIRECTORY_SEPARATOR).'/'.ltrim($path, DIRECTORY_SEPARATOR);

        $basedir = dirname($fullpath);
        if (!file_exists($basedir)) {
            @mkdir($basedir, 0777, true);
        }

        if (false === file_put_contents($fullpath, ['items' => $this->serializer->serialize($data, 'json')])) {
            throw new RuntimeException("An error occured while writing file.");
        }
    }
}
