<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Model\PhpVersion;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PhpVersionNormalizer implements NormalizerInterface
{
    /**
     * @param PhpVersion $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return $object->toArray();
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof PhpVersion;
    }
}
