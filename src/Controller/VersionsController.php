<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\{
    PDO\PdoPhpReleaseRepository,
    PDO\PdoPhpVersionRepository,
};
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Response,
};
use Symfony\Component\Serializer\SerializerInterface;

final class VersionsController
{
    private const CACHE_LIFETIME = 43_200; // 12h

    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    public function all(PdoPhpVersionRepository $repository): Response
    {
        $versions = $repository->all();

        return $this->createResponse($versions);
    }

    public function current(PdoPhpVersionRepository $repository): Response
    {
        $versions = $repository->maintenedVersions();

        return $this->createResponse($versions);
    }

    public function eol(PdoPhpVersionRepository $repository): Response
    {
        $versions = $repository->unmaintenedVersions();

        return $this->createResponse($versions);
    }

    public function releases(PdoPhpReleaseRepository $repository): Response
    {
        $releases = $repository->all();

        return $this->createResponse($releases);
    }

    private function createResponse(iterable $items): Response
    {
        $response = new JsonResponse([
            'items' => $this->serializer->normalize($items),
        ]);
        $response->setMaxAge(self::CACHE_LIFETIME);

        return $response;
    }
}
