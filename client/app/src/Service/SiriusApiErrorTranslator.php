<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Sirius\SiriusApiError;
use Symfony\Component\Serializer\SerializerInterface;

class SiriusApiErrorTranslator
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function translateApiError(string $errorString)
    {
        if ($this->jsonIsInUnexpectedFormat($errorString)) {
            return $errorString;
        }

        $apiError = $this->deserializeError($errorString);

        return sprintf('%s: %s', $apiError->getCode(), $apiError->getDetail());

    }

    private function deserializeError(string $errorString): SiriusApiError
    {
        $decodedJson = json_decode($errorString, true)['error'];

        return $this->serializer->deserialize(json_encode($decodedJson), SiriusApiError::class, 'json');
    }

    private function jsonIsInUnexpectedFormat(string $errorString): bool
    {
        $decodedJson = json_decode($errorString, true);

        return is_null($decodedJson) ||
        !array_key_exists('error', $decodedJson) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('code', $decodedJson['error']))) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('detail', $decodedJson['error']))) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('title', $decodedJson['error'])));
    }
}
