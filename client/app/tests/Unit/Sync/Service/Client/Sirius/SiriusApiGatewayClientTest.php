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
use Symfony\Component\Serializer\Serializer;
use Tests\OPG\Digideps\Frontend\Unit\Helpers\SiriusHelpers;

class SiriusApiGatewayClientTest extends KernelTestCase
{
    private const string SPEC_PATH = __DIR__ . '/../../../../../spec/deputy-reporting-openapi.yml';

    private function makeClient(RequestFixture $requestFixture): Client
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->atLeastOnce())->method('send')->willReturnCallback(function (RequestInterface $request, array $options = []) use ($requestFixture) {
            $validator = new ValidatorBuilder()->fromYamlFile(self::SPEC_PATH)->getRequestValidator();
            $failure = null;
            $validationResult = null;
            try {
                $validationResult = $validator->validate($request);
            } catch (ValidationFailed $validationFailed) {
                $failure = $validationFailed;
            }
            $this->assertNull($failure);
            $this->assertNotNull($validationResult);
            $this->assertSame($requestFixture->method, $validationResult->method());
            $this->assertSame($requestFixture->path, $validationResult->path());
            $this->assertSame($requestFixture->uri, "{$request->getUri()}");

            foreach ($requestFixture->headers as $header => $value) {
                $this->assertSame($value, $request->getHeaderLine($header));
            }
            $this->assertSame($requestFixture->body, "{$request->getBody()}");

            return $requestFixture->response;
        });
        return $client;
    }

    private function makeSiriusApiGatewayClient(RequestFixture $requestFixture): SiriusApiGatewayClient
    {
        $container = (self::bootKernel(['debug' => false]))->getContainer();
        /**
         * @var Serializer $serializer
         */
        $serializer = $container->get('serializer');

        return new SiriusApiGatewayClient(
            $this->makeClient($requestFixture),
            new RequestSigner(new DefaultCredentialProvider(), new SignatureV4Signer()),
            '',
            $serializer,
            $this->createStub(LoggerInterface::class)
        );
    }

    public function testSendReportPdfDocument(): void
    {
        $digidepsReportType = '102';
        $courtOrderUids = ['11122233', '22332244'];
        $caseRef = '1234567T';

        $reportStartDate = new \DateTime('2018-05-14', new \DateTimeZone('UTC'));
        $reportEndDate = new \DateTime('2019-05-13', new \DateTimeZone('UTC'));
        $reportSubmittedDate = new \DateTime('2019-06-20', new \DateTimeZone('UTC'));
        $reportSubmissionId = 9876;

        $filename = 'test.pdf';
        $s3Reference = 'dd_doc_98765_01234567890123';
        $reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            $reportSubmissionId,
            $filename,
            null,
            $s3Reference,
            $digidepsReportType,
            $courtOrderUids,
        );

        $response = new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode([
                'data' => ['id' => $reportPdfUuid],
            ])
        );

        $result = $this->makeSiriusApiGatewayClient(
            new RequestFixture(
                $response,
                'post',
                '/clients/{caseref}/reports',
                '/v2/clients/1234567T/reports',
                [
                    'Accept' => 'application/vnd.opg-data.v1+json',
                    'Content-type' => 'application/json'
                ],
                json_encode([
                    'report' => [
                        'data' => [
                            'type' => 'reports',
                            'attributes' => [
                                'reporting_period_from' => '2018-05-14',
                                'reporting_period_to' => '2019-05-13',
                                'date_submitted' => '2019-06-20T00:00:00+00:00',
                                'year' => 2018,
                                'submission_id' => 9876,
                                'type' => 'PF',
                                'digideps_report_type' => $digidepsReportType,
                                'court_order_uids' => $courtOrderUids,
                            ],
                            'file' => [
                                'name' => $filename,
                                'mimetype' => 'application/pdf',
                                's3_reference' => $s3Reference,
                            ],
                        ],
                    ],
                ])
            )
        )->sendReportPdfDocument($siriusDocumentUpload, $caseRef);

        $this->assertSame($response, $result);
    }

    public function testSendSupportingDocument(): void
    {
        $caseRef = '1234567T';

        $reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';

        $filename = 'test.pdf';
        $s3Reference = 'dd_doc_98765_01234567890123';

        $upload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            9876,
            $filename,
            null,
            $s3Reference
        );

        $response = new Response(201, ['Content-Type' => 'application/json'], json_encode([
            'data' => ['id' => $reportPdfUuid],
        ]));

        $result = $this->makeSiriusApiGatewayClient(
            new RequestFixture(
                $response,
                'post',
                '/clients/{caseref}/reports/{id}/supportingdocuments',
                '/v2/clients/1234567T/reports/33ea0382-cfc9-4776-9036-667eeb68fa4b/supportingdocuments',
                [
                    'Accept' => 'application/vnd.opg-data.v1+json',
                    'Content-type' => 'application/json'
                ],
                json_encode([
                    'supporting_document' => [
                        'data' => [
                            'type' => 'supportingdocuments',
                            'attributes' => [
                                'submission_id' => 9876,
                            ],
                            'file' => [
                                'name' => $filename,
                                'mimetype' => 'application/pdf',
                                's3_reference' => $s3Reference,
                            ],
                        ],
                    ],
                ])
            )
        )->sendSupportingDocument($upload, $reportPdfUuid, $caseRef);

        $this->assertSame($response, $result);
    }

    public function testPostChecklistPdf(): void
    {
        $caseRef = '1234567T';
        $reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';
        $filename = 'test.pdf';
        $fileContents = 'fake_contents';
        $submitterEmail = 'donald.draper@digital.justice.gov.uk';
        $checklistPdfUuid = '9c0cb55e-718d-4ffb-9599-f3164e132ab5';

        $upload = SiriusHelpers::generateSiriusChecklistPdfUpload(
            $filename,
            $fileContents,
            11112,
            $submitterEmail,
            new \DateTime('2019-06-01', new \DateTimeZone('UTC')),
            new \DateTime('2020-05-31', new \DateTimeZone('UTC')),
            2020,
            'PF'
        );

        $response = new Response(201, ['Content-Type' => 'application/json'], json_encode([
            'data' => ['id' => $checklistPdfUuid],
        ]));

        $result = $this->makeSiriusApiGatewayClient(
            new RequestFixture(
                $response,
                'post',
                '/clients/{caseref}/reports/{id}/checklists',
                '/v2/clients/1234567T/reports/33ea0382-cfc9-4776-9036-667eeb68fa4b/checklists',
                [
                    'Accept' => 'application/vnd.opg-data.v1+json',
                    'Content-type' => 'application/json'
                ],
                json_encode([
                'checklist' => [
                    'data' => [
                        'type' => 'checklists',
                        'attributes' => [
                            'submission_id' => 11112,
                            'year' => 2020,
                            'submitter_email' => $submitterEmail,
                            'type' => 'PF',
                            'reporting_period_from' => '2019-06-01',
                            'reporting_period_to' => '2020-05-31',
                        ],
                        'file' => [
                            'name' => $filename,
                            'mimetype' => 'application/pdf',
                            'source' => base64_encode($fileContents),
                        ],
                    ],
                ],
                ])
            )
        )->postChecklistPdf($upload, $reportPdfUuid, $caseRef);

        $this->assertSame($response, $result);
    }

    public function testGet(): void
    {
        $this->makeSiriusApiGatewayClient(new RequestFixture(
            new Response(),
            'get',
            '/healthcheck',
            '/v2/healthcheck',
            [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ],
            ''
        ))->get('healthcheck');
    }

    public function testPutChecklistPdf(): void
    {
        $caseRef = '1234567T';
        $reportPdfUuid = '33ea0382-cfc9-4776-9036-667eeb68fa4b';
        $checklistPdfUuid = '9c0cb55e-718d-4ffb-9599-f3164e132ab5';
        $filename = 'test.pdf';
        $fileContents = 'fake_contents';
        $submitterEmail = 'donald.draper@digital.justice.gov.uk';

        $upload = SiriusHelpers::generateSiriusChecklistPdfUpload(
            $filename,
            $fileContents,
            11112,
            $submitterEmail,
            new \DateTime('2019-06-01'),
            new \DateTime('2020-05-31'),
            2020,
            'PF'
        );

        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'data' => ['id' => $checklistPdfUuid],
         ]));

        $result = $this->makeSiriusApiGatewayClient(
            new RequestFixture(
                $response,
                'put',
                '/clients/{caseref}/reports/{id}/checklists/{checklistId}',
                '/v2/clients/1234567T/reports/33ea0382-cfc9-4776-9036-667eeb68fa4b/checklists/9c0cb55e-718d-4ffb-9599-f3164e132ab5',
                [
                    'Accept' => 'application/vnd.opg-data.v1+json',
                    'Content-type' => 'application/json'
                ],
                json_encode([
                    'checklist' => [
                        'data' => [
                            'type' => 'checklists',
                            'attributes' => [
                                'submission_id' => 11112,
                                'year' => 2020,
                                'submitter_email' => $submitterEmail,
                                'type' => 'PF',
                                'reporting_period_from' => '2019-06-01',
                                'reporting_period_to' => '2020-05-31',
                            ],
                            'file' => [
                                'name' => $filename,
                                'mimetype' => 'application/pdf',
                                'source' => base64_encode($fileContents),
                            ],
                        ],
                    ],
                ])
            )
        )->putChecklistPdf($upload, $reportPdfUuid, $caseRef, $checklistPdfUuid);

        $this->assertSame($response, $result);
    }
}
