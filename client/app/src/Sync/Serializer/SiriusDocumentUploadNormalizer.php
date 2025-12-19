<?php

declare(strict_types=1);

namespace App\Sync\Serializer;

use App\Model\Sirius\SiriusDocumentUpload;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SiriusDocumentUploadNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @throws \Exception
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        /** @var array|string|int|float|bool|null $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (isset($data['attributes']['reporting_period_from'])) {
            $data['attributes']['reporting_period_from'] = (new \DateTime($data['attributes']['reporting_period_from']))->format('Y-m-d');
        }

        if (isset($data['attributes']['reporting_period_to'])) {
            $data['attributes']['reporting_period_to'] = (new \DateTime($data['attributes']['reporting_period_to']))->format('Y-m-d');
        }

        return $data;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data Data to normalize
     * @param ?string $format The format being (de-)serialized from or into
     */
    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof SiriusDocumentUpload;
    }
}
