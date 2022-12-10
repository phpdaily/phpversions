<?php

declare(strict_types=1);

namespace App;

use App\{
    Model\PhpRelease,
    Model\PhpVersion,
};
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhpVersionFetcher
{
    private const SUPPORTED_VERSIONS_URL = 'https://www.php.net/supported-versions';
    private const END_OF_LIFE_URL = 'https://www.php.net/eol.php';
    private const RELEASES_URL = 'https://www.php.net/releases/index.php';
    private const STABLE_VERSIONS = 'https://www.php.net/downloads';

    public function __construct(
        private HttpClientInterface $http,
    ) {
    }

    /**
     * @return PhpVersion[]
     */
    public function currents(): iterable
    {
        $versions = new Collection();
        $releases = $this->releases();

        $response = $this->http->request('GET', self::SUPPORTED_VERSIONS_URL);

        $crawler = new Crawler($response->getContent(true));
        $supportedVersionsTable = $crawler->filter('table')->first()->filter('tbody tr');
        foreach ($supportedVersionsTable as $row) {
            $cols = (new Crawler($row))->filter('td');

            $version = $cols->eq(0)->text();
            $initialReleaseDate = new DateTimeImmutable($cols->eq(1)->text());
            $activeSupportUntil = new DateTimeImmutable($cols->eq(3)->text());
            $securitySupportUntil = new DateTimeImmutable($cols->eq(5)->text());

            $lastRelease = $releases->first(static fn(PhpRelease $release): bool => $version !== $release->getVersion() && $version === substr($release->getVersion(), 0, strlen($version)));

            $versions[$version] = new PhpVersion(
                $version,
                lastRelease: $lastRelease?->getVersion(),
                initialRelease: $initialReleaseDate,
                activeSupportUntil: $activeSupportUntil,
                endOfLife: $securitySupportUntil,
            );
        }

        return $versions;
    }

    /**
     * @return PhpVersion[]
     */
    public function eol(): iterable
    {
        $versions = new Collection();
        $releases = $this->releases();

        $response = $this->http->request('GET', self::END_OF_LIFE_URL);

        $crawler = new Crawler($response->getContent(true));
        $unsupportedVersionsTable = $crawler->filter('table')->first()->filter('tbody tr');
        foreach ($unsupportedVersionsTable as $row) {
            $cols = (new Crawler($row))->filter('td');

            $version = $cols->eq(0)->text();
            $endOfLife = $this->extractDate($cols->eq(1)->text());
            $lastRelease = $cols->eq(3)->text();

            $initialVersion = "3.0" !== $version ? "$version.0" : "$version.x";

            $release = $releases[$initialVersion] ?? null;
            $initialRelease = $release?->getReleaseDate();

            $versions[$version] = new PhpVersion(
                $version,
                $lastRelease,
                initialRelease: $initialRelease,
                endOfLife: $endOfLife,
            );
        }

        return $versions->reverse();
    }

    /**
     * @return array<string, DateTimeImmutable>
     */
    public function releases(): iterable
    {
        $releases = new Collection();

        // stable versions
        $response = $this->http->request('GET', self::STABLE_VERSIONS);

        $crawler = new Crawler($response->getContent(true));
        $content = $crawler->filter('h3');
        foreach ($content as $key => $element) {
            if ('gpg' === substr($element->attributes['id']->value, 0,3)) {
                continue;
            }

            $version = substr($element->attributes['id']->value, 1);
            $releaseDateText = $crawler->filter('.content-box')->eq($key)->filter('.releasedate')->first()->text();

            $releases[$version] = new PhpRelease(
                $version,
                new DateTimeImmutable($releaseDateText),
            );
        }

        // unsupported historical releases
        $response = $this->http->request('GET', self::RELEASES_URL);

        $crawler = new Crawler($response->getContent(true));
        $releasesContent = $crawler->filter('#layout-content')->children();
        foreach ($releasesContent as $key => $element) {
            if ('h2' !== $element->nodeName) {
                continue;
            }

            $version = $element->textContent;
            $releaseDateText = str_replace('Released: ', '', $releasesContent->eq($key+1)->filter('ul li')->first()->text());

            $releases[$version] = new PhpRelease(
                $version,
                new DateTimeImmutable($releaseDateText),
            );
        }

        return $releases;
    }

    private function extractDate(string $str): DateTimeImmutable
    {
        $date = substr($str, 0, strpos($str, '(') ?? strlen($str));

        return new DateTimeImmutable($date);
    }
}
