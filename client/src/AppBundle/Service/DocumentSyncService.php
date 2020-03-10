<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusDocumentFile;
use AppBundle\Service\Client\Sirius\SiriusDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Symfony\Component\HttpFoundation\Response;

class DocumentSyncService
{
    const PERMANENT_ERRORS = [
        Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
    ];

    /** @var S3Storage */
    private $storage;

    /**
     * @var SiriusApiGatewayClient
     */
    private $siriusApiGateWayClient;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(
        S3Storage $storage,
        SiriusApiGatewayClient $siriusApiGateWayClient,
        RestClient $restClient
    )
    {
        $this->storage = $storage;
//        $this->client = new Client([
//            'base_uri' => 'http://pact-mock'
//        ]);
        $this->siriusApiGateWayClient = $siriusApiGateWayClient;
        $this->restClient = $restClient;
    }

    public function syncReportDocument(Document $document)
    {
        $content = $this->storage->retrieve($document->getStorageReference());
        /** @var Report $report */
        $report = $document->getReport();
        $upload = $this->buildUpload($document, $content);

        try {
            $response = $this->siriusApiGateWayClient->sendDocument($upload);

            $data = json_decode(strval($response->getBody()), true);

            if ($data['data']['uuid'])  {
                /** @var ReportSubmission $latestSubmission */
                $latestSubmission = $report->getReportSubmissions()[0];
                $submissionId = $latestSubmission->getId();

                $this->restClient->put(
                    sprintf('report-submission/%s', $submissionId),
                    json_encode(['uuid' => $data['data']['uuid']])
                );
            }

            return $data['data']['uuid'];
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response) {
                $response = json_decode($exception->getResponse()->getBody());

                return $response->errors[0]->detail;
            } else {
                return $exception->getMessage();
            }
        }
    }

    private function buildUpload(Document $document, string $content)
    {
        $report = $document->getReport();

        $siriusDocumentMetadata = (new SiriusDocumentMetadata())
            ->setReportingPeriodFrom($report->getStartDate())
            ->setReportingPeriodTo($this->determineEndDate($report))
            ->setYear($report->getStartDate()->format('Y'))
            ->setDateSubmitted($report->getSubmitDate())
            ->setOrderType($this->determineReportType($report));

        $siriusDocumentFile = (new SiriusDocumentFile())
            ->setFileName($document->getFileName())
            ->setMimeType($document->getFile()->getClientMimeType())
            ->setSource(base64_encode($content));

        return (new SiriusDocumentUpload())
            ->setCaseRef($report->getClient()->getCaseNumber())
            ->setDocumentType('Report')
            ->setDocumentSubType('Report')
            ->setDirection('DIRECTION_INCOMING')
            ->setMetadata($siriusDocumentMetadata)
            ->setFile($siriusDocumentFile);
    }

    private function determineReportType(Report $report)
    {
        if ($report instanceof Ndr) {
            return 'NDR';
        } else if (in_array($report->getType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
            return 'HW';
        } else {
            return 'PF';
        }
    }

    public function determineEndDate(Report $report)
    {
        return $report instanceof Ndr ? $report->getStartDate() : $report->getEndDate();
    }

    /**
     * @param Report|Ndr $report
     * @return array
     */
    private function getAttributesFromReport(ReportInterface $report): array
    {
//        if ($report instanceof Ndr) {
//            $type = 'NDR';
//        } else if (in_array($report->getType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
//            $type = 'HW';
//        } else {
//            $type = 'PF';
//        }

        if ($report instanceof Ndr) {
            $endDate = $report->getStartDate()->format('Y-m-d');
        } else {
            $endDate = $report->getEndDate()->format('Y-m-d');
        }

        return [
            'reporting_period_from' => $report->getStartDate()->format('Y-m-d'),
            'reporting_period_to' => $endDate,
            'year' => $report->getStartDate()->format('Y'),
            'date_submitted' => $report->getSubmitDate()->format(DateTime::ATOM),
            'type' => $type
        ];
    }

    private function sendReportDocument($caseRef, $contents, $attributes): Psr7Response
    {
        $data = json_encode([
            'data' => [
                'type' => 'reports',
                'attributes' => $attributes,
            ]
        ]);

        return $this->client->request('POST', "/clients/$caseRef/reports", [
            'multipart' => [
                [
                    'name' => 'report',
                    'contents' => $data,
                ],
                [
                    'name' => 'report_file',
                    'contents' => $contents
                ]
            ],
        ]);
    }
}
