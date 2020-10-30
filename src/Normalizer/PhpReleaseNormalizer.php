<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\PhpRelease;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PhpReleaseNormalizer implements NormalizerInterface
{
    /**
     * @param PhpRelease $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        return $object->toArray();
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof PhpRelease;
    }
}
