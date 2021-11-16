<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Model\Sirius\SiriusDocumentUpload;
use DateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SiriusDocumentUploadSerializer implements NormalizerInterface
{
    public function __construct(private ObjectNormalizer $normalizer)
    {
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param $metaData
     * @param string $format  Format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|string|int|float|bool|null
     *
     * @throws \Exception
     */
    public function normalize($metaData, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($metaData, $format, $context);

        if (isset($data['attributes']['reporting_period_from'])) {
            $data['attributes']['reporting_period_from'] = (new DateTime($data['attributes']['reporting_period_from']))->format('Y-m-d');
        }

        if (isset($data['attributes']['reporting_period_to'])) {
            $data['attributes']['reporting_period_to'] = (new DateTime($data['attributes']['reporting_period_to']))->format('Y-m-d');
        }

        if (is_null($data['file']['source'])) {
            unset($data['file']['source']);
        }

        if (is_null($data['file']['s3_reference'])) {
            unset($data['file']['s3_reference']);
        }

        return $data;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data   Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SiriusDocumentUpload;
    }
}
