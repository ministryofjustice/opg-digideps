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

        $translations = [
            'OPGDATA-API-FORBIDDEN' => 'Credentials used for integration lack correct permissions',
            'OPGDATA-API-API_CONFIGURATION_ERROR' => 'Integration API internal error',
            'OPGDATA-API-AUTHORIZER_CONFIGURATION_ERROR' => 'Integration API internal error',
            'OPGDATA-API-AUTHORIZER_FAILURE' => 'Integration API internal error',
            'OPGDATA-API-INVALIDREQUEST' => 'The body of the request is not valid',
            'OPGDATA-API-BAD_REQUEST_PARAMETERS' => 'The parameters of the request are not valid',
            'OPGDATA-API-SERVERERROR' => 'Integration API server error',
            'OPGDATA-API-EXPIRED_TOKEN' => 'Auth token has expired',
            'OPGDATA-API-INTEGRATION_FAILURE' => 'There was a problem syncing from the integration to Sirius',
            'OPGDATA-API-INTEGRATION_TIMEOUT' => 'The sync process timed out while communicating with Sirius',
            'OPGDATA-API-INVALID_API_KEY' => 'The API key used in the request is not valid',
            'OPGDATA-API-INVALID_SIGNATURE' => 'The signature of the request is not valid',
            'OPGDATA-API-MISSING_AUTHENTICATION_TOKEN' => 'Authentication token is missing from the request',
            'OPGDATA-API-QUOTA_EXCEEDED' => 'API quota has been exceeded',
            'OPGDATA-API-FILESIZELIMIT' => 'The size of the file exceeded the file size limit (6MB)',
            'OPGDATA-API-NOTFOUND' => 'Invalid URL used during integration or the resource no longer exists',
            'OPGDATA-API-THROTTLED' => 'Too many requests made - throttling in action',
            'OPGDATA-API-UNAUTHORISED' => 'No user/auth provided during requests',
            'OPGDATA-API-MEDIA' => 'Media type of the file is not supported',
            'OPGDATA-API-WAF_FILTERED' => 'AWS WAF filtered this request and it was not sent to Sirius',
        ];

        if (is_null($apiError->getCode()) || is_null($translations[$apiError->getCode()])) {
            return $errorString;
        } else {
            return sprintf('%s: %s', $apiError->getCode(), $translations[$apiError->getCode()]);
        }
    }

    private function deserializeError(string $errorString): \App\Model\Sirius\SiriusApiError|string
    {
        $decodedJson = json_decode($errorString, true)['body']['error'];

        return $this->serializer->deserialize(json_encode($decodedJson), 'App\Model\Sirius\SiriusApiError', 'json');
    }

    private function jsonIsInUnexpectedFormat(string $errorString)
    {
        $decodedJson = json_decode($errorString, true);

        return is_null($decodedJson) ||
        !array_key_exists('body', $decodedJson) ||
        (array_key_exists('body', $decodedJson) && (!array_key_exists('error', $decodedJson['body'])));
    }
}
