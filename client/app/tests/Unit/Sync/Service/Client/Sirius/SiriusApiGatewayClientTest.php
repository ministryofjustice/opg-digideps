<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Service\Client\Sirius;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use OPG\Digideps\Frontend\Service\AWS\DefaultCredentialProvider;
use OPG\Digideps\Frontend\Service\AWS\RequestSigner;
use OPG\Digideps\Frontend\Service\AWS\SignatureV4Signer;
use OPG\Digideps\Frontend\Sync\Service\Client\Sirius\SiriusApiGatewayClient;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusApiGatewayClientTest extends KernelTestCase
{
    private const string SPEC_PATH = __DIR__ . '/../../../../../spec/deputy-reporting-openapi.yml';

    private function makeClient(): Client
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->atLeastOnce())->method('send')->willReturnCallback(function (RequestInterface $request, array $options = []) {
            $validator = new ValidatorBuilder()->fromYamlFile(self::SPEC_PATH)->getRequestValidator();
            $failure = null;
            try {
                $result = $validator->validate($request);
            } catch (ValidationFailed $validationFailed) {
                $failure = $validationFailed;
            }
            $this->assertNull($failure);
            return new Response(200, $request->getHeaders(), $request->getBody());
        });
        return $client;
    }


    private function makeSiriusApiGatewayClient(): SiriusApiGatewayClient
    {
        $container = (self::bootKernel(['debug' => false]))->getContainer();

        return new SiriusApiGatewayClient(
            $this->makeClient(),
            new RequestSigner(new DefaultCredentialProvider(), new SignatureV4Signer()),
            '',
            $container->get('serializer'),
            $this->createStub(LoggerInterface::class)
        );
    }

    public function testSendReportPdfDocument(): void
    {
    }

    public function testSendSupportingDocument(): void
    {
    }

    public function testPostChecklistPdf(): void
    {
    }

    public function testGet(): void
    {
        $this->makeSiriusApiGatewayClient()->get('healthcheck');
    }

    public function testPutChecklistPdf(): void
    {
    }
}
