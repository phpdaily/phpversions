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

        $this->writeContent('index.html', <<<HTML
<html>
    <head>
        <title>phpversions</title>
    </head>
    <body>
        <p>An API that expose all PHP versions:</p>
        <ul>
            <li><a href="/all.json">All main PHP versions</a></li>
            <li><a href="/releases.json">All PHP releases versions</a></li>
            <li><a href="/maintened.json">Maintened versions</a></li>
            <li><a href="/unmaintened.json">Unmainted versions</a></li>
        </ul>
        <p>Last update: {$this->clock->now()->format('Y-m-d H:i:s')}</p>
    </body>
</html>
HTML);
    }

    private function writeAllVersionsFile(iterable $versions): void
    {
        $this->writeJson('all.json', $versions);
    }

    /**
     * @param iterable<PhpVersion> $versions
     */
    private function writeMaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))->filter(
            fn (PhpVersion $version): bool => $version->getEndOfLife() > $this->clock->now(),
        );

        $this->writeJson('maintened.json', $data);
    }

    private function writeUnmaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))->filter(
            fn (PhpVersion $version): bool => $version->getEndOfLife() <= $this->clock->now(),
        );

        $this->writeJson('unmaintened.json', $data);
    }

    private function writeReleaseVersions(iterable $releases): void
    {
        $this->writeJson('releases.json', $releases);
    }

    private function writeJson(string $file, iterable $data): void
    {
        $this->writeContent($file, ['items' => $this->serializer->serialize($data, 'json')]);
    }

    private function writeContent(string $file, iterable|string $data): void
    {
        $fullpath = rtrim($this->outputFolder, DIRECTORY_SEPARATOR).'/'.ltrim($file, DIRECTORY_SEPARATOR);

        $basedir = dirname($fullpath);
        if (!file_exists($basedir)) {
            @mkdir($basedir, 0777, true);
        }

        if (false === file_put_contents($fullpath, $data)) {
            throw new RuntimeException("An error occured while writing file.");
        }
    }
}
