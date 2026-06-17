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
use Tests\OPG\Digideps\Frontend\Unit\Helpers\SiriusHelpers;

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
                $validator->validate($request);
            } catch (ValidationFailed $validationFailed) {
                $failure = $validationFailed;
            }
            $this->assertNull($failure);
            return new Response(200, [
                'X-Request-Uri' => "{$request->getUri()}",
                'X-Request-Method' => $request->getMethod(),
                ...$request->getHeaders()], $request->getBody());
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
        $this->markTestIncomplete();
    }

    public function testSendSupportingDocument(): void
    {
        $this->markTestIncomplete();
    }

    public function testPostChecklistPdf(): void
    {
        $this->markTestIncomplete();
    }

    public function testGet(): void
    {
        $this->makeSiriusApiGatewayClient()->get('healthcheck');
    }

    public function testPutChecklistPdf(): void
    {
        $caseRef = '1234567T';
        $reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';
        $checklistPdfUuid = '9c0cb55e-718d-4ffb-9599-f3164e132ab5';
        $fileName = 'test.pdf';
        $fileContents = 'fake_contents';
        $submitterEmail = 'donald.draper@digital.justice.gov.uk';

        $upload = SiriusHelpers::generateSiriusChecklistPdfUpload(
            $fileName,
            $fileContents,
            11112,
            $submitterEmail,
            new \DateTime('2019-06-01'),
            new \DateTime('2020-05-31'),
            2020,
            'PF'
        );

        $result = $this->makeSiriusApiGatewayClient()->putChecklistPdf($upload, $reportPdfUuid, $caseRef, $checklistPdfUuid);

        $this->assertStringContainsString(
            $checklistPdfUuid,
            $result->getHeaderLine('X-Request-Uri')
        );
    }
}
