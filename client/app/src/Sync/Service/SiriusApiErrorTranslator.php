<?php

declare(strict_types=1);

namespace App\Sync\Service;

use App\Sync\Model\Sirius\SiriusApiError;
use Symfony\Component\Serializer\SerializerInterface;

class SiriusApiErrorTranslator
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function translateApiError(string $errorString): string
    {
        if ($this->jsonIsInUnexpectedFormat($errorString)) {
            return $errorString;
        }

        $apiError = $this->deserializeError($errorString);

        return sprintf('%s: %s', $apiError->getCode(), $apiError->getDetail());
    }

    private function deserializeError(string $errorString): SiriusApiError
    {
        /** @var array $decodedError */
        $decodedError = json_decode($errorString, true);

        return $this->serializer->deserialize(json_encode($decodedError['error']), SiriusApiError::class, 'json');
    }

    private function jsonIsInUnexpectedFormat(string $errorString): bool
    {
        /** @var ?array $decodedJson */
        $decodedJson = json_decode($errorString, true);

        return is_null($decodedJson) ||
            !array_key_exists('error', $decodedJson) ||
            !array_key_exists('code', $decodedJson['error']) ||
            !array_key_exists('detail', $decodedJson['error']) ||
            !array_key_exists('title', $decodedJson['error']);
    }
}
