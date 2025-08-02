<?php

declare(strict_types=1);

namespace App\Storage;

use App\Clock;
use App\Model\PhpRelease;
use App\Model\PhpVersion;
use App\Storage;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;
use function BenTools\IterableFunctions\iterable_to_array;

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
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PHP Versions API</title>
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                line-height: 1.6;
                color: #333;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .header p {
                font-size: 1.1rem;
                opacity: 0.9;
            }
            .content {
                padding: 30px;
            }
            .deprecated-banner {
                background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
                color: #92400e;
                padding: 20px;
                margin-bottom: 30px;
                border-radius: 10px;
                border-left: 5px solid #d97706;
                font-weight: 600;
            }
            .deprecated-banner a {
                color: #92400e;
                text-decoration: underline;
            }
            .api-list {
                display: grid;
                gap: 15px;
                margin-bottom: 30px;
            }
            .api-item {
                background: #f8fafc;
                border: 2px solid #e2e8f0;
                border-radius: 10px;
                padding: 20px;
                transition: all 0.3s ease;
                text-decoration: none;
                color: #334155;
                display: block;
            }
            .api-item:hover {
                border-color: #4f46e5;
                background: #f1f5f9;
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
            }
            .api-item h3 {
                font-size: 1.2rem;
                font-weight: 600;
                margin-bottom: 5px;
                color: #1e293b;
            }
            .api-item p {
                color: #64748b;
                font-size: 0.9rem;
            }
            .footer {
                text-align: center;
                padding: 20px 30px;
                background: #f8fafc;
                border-top: 1px solid #e2e8f0;
                color: #64748b;
                font-size: 0.9rem;
            }
            .php-logo {
                display: inline-block;
                font-size: 3rem;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="php-logo">🐘</div>
                <h1>PHP Versions API</h1>
                <p>Access comprehensive PHP version information</p>
            </div>

            <div class="content">
                <div class="deprecated-banner">
                    <strong>⚠️ DEPRECATED:</strong> This API is deprecated in favour of the official JSON available at <a href="https://www.php.net/releases/states.php" target="_blank">https://www.php.net/releases/states.php</a>
                </div>

                <div class="api-list">
                    <a href="/all.json" class="api-item">
                        <h3>📋 All PHP Versions</h3>
                        <p>Complete list of all main PHP versions with details</p>
                    </a>
                    <a href="/releases.json" class="api-item">
                        <h3>🚀 All Releases</h3>
                        <p>Comprehensive list of all PHP release versions</p>
                    </a>
                    <a href="/maintened.json" class="api-item">
                        <h3>✅ Maintained Versions</h3>
                        <p>Currently supported and maintained PHP versions</p>
                    </a>
                    <a href="/unmaintened.json" class="api-item">
                        <h3>❌ Unmaintained Versions</h3>
                        <p>End-of-life PHP versions no longer supported</p>
                    </a>
                </div>
            </div>

            <div class="footer">
                <p>Last update: {$this->clock->now()->format('Y-m-d H:i:s')}</p>
            </div>
        </div>
    </body>
</html>
HTML);
    }

    /**
     * @param iterable<PhpVersion> $versions
     */
    private function writeAllVersionsFile(iterable $versions): void
    {
        $data = (new Collection($versions))
            ->sort(static fn (PhpVersion $item1, PhpVersion $item2): int => -1 * version_compare($item1->getVersion(), $item2->getVersion()))
            ->values();

        $this->writeJson('all.json', $data);
    }

    /**
     * @param iterable<PhpVersion> $versions
     */
    private function writeMaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))
            ->filter(fn (PhpVersion $version): bool => $version->getEndOfLife() > $this->clock->now())
            ->sort(static fn (PhpVersion $item1, PhpVersion $item2): int => -1 * version_compare($item1->getVersion(), $item2->getVersion()))
            ->values();

        $this->writeJson('maintened.json', $data);
    }

    private function writeUnmaintenedVersions(iterable $versions): void
    {
        $data = (new Collection($versions))
            ->filter(fn (PhpVersion $version): bool => $version->getEndOfLife() <= $this->clock->now())
            ->sort(static fn (PhpVersion $a, PhpVersion $b): int => -1 * version_compare($a->getVersion(), $b->getVersion()))
            ->values();

        $this->writeJson('unmaintened.json', $data);
    }

    private function writeReleaseVersions(iterable $releases): void
    {
        $data = (new Collection($releases))
            ->sort(static fn (PhpRelease $a, PhpRelease $b): int => -1 * ($a->getReleaseDate() <=> $b->getReleaseDate()))
            ->values();

        $this->writeJson('releases.json', $data);
    }

    private function writeJson(string $file, iterable $data): void
    {
        $this->writeContent($file, $this->serializer->serialize(['items' => $data], 'json'));
    }

    private function writeContent(string $file, string $data): void
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
