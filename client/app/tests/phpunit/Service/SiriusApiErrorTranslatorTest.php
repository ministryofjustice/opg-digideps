<?php declare(strict_types=1);


use App\Service\SiriusApiErrorTranslator;
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
     */
    public function translateApiErrors_unexpected_format()
    {
        $sut = new SiriusApiErrorTranslator($this->serializer);
        $unexpectedErrorJson = '{"An error occurred"}';
        $translation = $sut->translateApiError($unexpectedErrorJson);

        self::assertEquals($unexpectedErrorJson, $translation);
    }

     /**
      * @test
      */
     public function translateApiError()
     {

         $sut = new SiriusApiErrorTranslator($this->serializer);

         $errorJson = '{
               "error":{
                  "code":"OPGDATA-API-INVALIDREQUEST",
                  "detail":"400 Bad Request: {\'validation_errors\': {\'file -> source\': {\'isEmpty\': \"Value is required and can\'t be empty\"}}, \'type\': \'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html\', \'title\': \'Bad Request\', \'status\': 400, \'detail\': \'Payload failed validation\'}",
                  "title":"Invalid Request"
               },
               "headers":{
                    "Content-Type":"application/json"
               },
               "isBase64Encoded":false,
               "statusCode":400
            }';
         $translation = $sut->translateApiError($errorJson);
         $expectedError = "OPGDATA-API-INVALIDREQUEST: 400 Bad Request: {'validation_errors': {'file -> source': {'isEmpty': \"Value is required and can't be empty\"}}, 'type': 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', 'title': 'Bad Request', 'status': 400, 'detail': 'Payload failed validation'}";

         self::assertEquals($expectedError, $translation);
     }
}
