<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Sirius\SiriusApiError;
use Symfony\Component\Serializer\SerializerInterface;

class SiriusApiErrorTranslator
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function translateApiError(string $errorString)
    {
        if ($this->jsonIsInUnexpectedFormat($errorString)) {
            return $errorString;
        }

        $apiError = $this->deserializeError($errorString);

        return sprintf('%s: %s', $apiError->getCode(), $apiError->getDetail());

    }

    /**
     * @return SiriusApiError|string
     */
    private function deserializeError(string $errorString)
    {
        $decodedJson = json_decode($errorString, true)['error'];

        return $this->serializer->deserialize(json_encode($decodedJson), 'App\Model\Sirius\SiriusApiError', 'json');
    }

    private function jsonIsInUnexpectedFormat(string $errorString)
    {
        $decodedJson = json_decode($errorString, true);

        return is_null($decodedJson) ||
        !array_key_exists('error', $decodedJson) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('code', $decodedJson['error']))) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('detail', $decodedJson['error']))) ||
        (array_key_exists('error', $decodedJson) && (!array_key_exists('title', $decodedJson['error'])));
    }
}
