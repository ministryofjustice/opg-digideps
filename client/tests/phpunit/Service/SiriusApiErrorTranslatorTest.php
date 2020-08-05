<?php declare(strict_types=1);


use AppBundle\Service\SiriusApiErrorTranslator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SiriusApiErrorTranslatorTest extends KernelTestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function setUp(): void
    {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @test
     * @dataProvider errorProvider
     */
    public function translateApiErrors(?string $apiErrorCode, string $expectedTranslation)
    {
        $sut = new SiriusApiErrorTranslator($this->serializer);

        $errorJson = sprintf('{"errors":{"id":"7d0bb9c2-76c5-4cd1-b7a4-6cc28acc197f","code":"%s","title":"Request Too Long","detail":"","meta":{"x-ray":""}}}', $apiErrorCode);
        $translation = $sut->translateApiError($errorJson);
        $expectedError = sprintf('%s: %s', $apiErrorCode, $expectedTranslation);

        self::assertEquals($expectedError, $translation);
    }

    public function errorProvider()
    {
        return [
            'ACCESS_DENIED' => ['OPGDATA-API-FORBIDDEN', 'Credentials used for integration lack correct permissions'],
            'API_CONFIGURATION_ERROR' => ['OPGDATA-API-API_CONFIGURATION_ERROR', 'Integration API internal error'],
            'AUTHORIZER_CONFIGURATION_ERROR' => ['OPGDATA-API-AUTHORIZER_CONFIGURATION_ERROR', 'Integration API internal error'],
            'AUTHORIZER_FAILURE' => ['OPGDATA-API-AUTHORIZER_FAILURE', 'Integration API internal error'],
            'BAD_REQUEST_BODY' => ['OPGDATA-API-INVALIDREQUEST', 'The body of the request is not valid'],
            'BAD_REQUEST_PARAMETERS' => ['OPGDATA-API-BAD_REQUEST_PARAMETERS', 'The parameters of the request are not valid'],
            'DEFAULT_5XX' => ['OPGDATA-API-SERVERERROR', 'Integration API server error'],
            'EXPIRED_TOKEN' => ['OPGDATA-API-EXPIRED_TOKEN', 'Auth token has expired'],
            'INTEGRATION_FAILURE' => ['OPGDATA-API-INTEGRATION_FAILURE', 'There was a problem syncing from the integration to Sirius'],
            'INTEGRATION_TIMEOUT' => ['OPGDATA-API-INTEGRATION_TIMEOUT', 'The sync process timed out while communicating with Sirius'],
            'INVALID_API_KEY' => ['OPGDATA-API-INVALID_API_KEY', 'The API key used in the request is not valid'],
            'INVALID_SIGNATURE' => ['OPGDATA-API-INVALID_SIGNATURE', 'The signature of the request is not valid'],
            'MISSING_AUTHENTICATION_TOKEN' => ['OPGDATA-API-MISSING_AUTHENTICATION_TOKEN', 'Authentication token is missing from the request'],
            'QUOTA_EXCEEDED' => ['OPGDATA-API-QUOTA_EXCEEDED', 'API quota has been exceeded'],
            'REQUEST_TOO_LARGE' => ['OPGDATA-API-FILESIZELIMIT', 'The size of the file exceeded the file size limit (6MB)'],
            'RESOURCE_NOT_FOUND' => ['OPGDATA-API-NOTFOUND', 'Invalid URL used during integration or the resource no longer exists'],
            'THROTTLED' => ['OPGDATA-API-THROTTLED', 'Too many requests made - throttling in action'],
            'UNAUTHORIZED' => ['OPGDATA-API-UNAUTHORISED', 'No user/auth provided during requests'],
            'UNSUPPORTED_MEDIA_TYPE' => ['OPGDATA-API-MEDIA', 'Media type of the file is not supported'],
            'WAF_FILTERED' => ['OPGDATA-API-WAF_FILTERED', 'AWS WAF filtered this request and it was not sent to Sirius'],
        ];
    }

    /**
     * @test
     */
    public function translateApiErrors_unexpected_error_code()
    {
        $sut = new SiriusApiErrorTranslator($this->serializer);

        $errorJson = '{"errors":{"id":"7d0bb9c2-76c5-4cd1-b7a4-6cc28acc197f","code":"AN UNEXPECTED CODE","title":"Request Too Long","detail":"","meta":{"x-ray":""}}}';
        $translation = $sut->translateApiError($errorJson);
        $expectedError = 'UNEXPECTED ERROR CODE: An unknown error occurred during document sync';

        self::assertEquals($expectedError, $translation);
    }

    /** @test */
    public function translateApiErrors_can_handle_non_json()
    {
        $sut = new SiriusApiErrorTranslator($this->serializer);

        $errorJson = 'An error that is not JSON';
        $translation = $sut->translateApiError($errorJson);
        $expectedError = 'An error that is not JSON';

        self::assertEquals($expectedError, $translation);
    }

    /** @test */
    public function translateApiErrors_can_handle_json_in_unexpected_format()
    {
        $sut = new SiriusApiErrorTranslator($this->serializer);

        $errorJson = '{"data":{"error":"Something went wrong"}}';
        $translation = $sut->translateApiError($errorJson);
        $expectedError = '{"data":{"error":"Something went wrong"}}';

        self::assertEquals($expectedError, $translation);
    }
}
