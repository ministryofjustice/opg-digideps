<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
        $this->client = new Client([
            'base_uri' => 'http://pact-mock'
        ]);
        $this->siriusApiGateWayClient = $siriusApiGateWayClient;
        $this->restClient = $restClient;
    }

    /**
     * @param Document $document
     * @return string
     */
    public function syncReportDocument(Document $document)
    {
        /** @var Report $report */
        $report = $document->getReport();

        if (!$document->canBeSynced()) {
            return $this->restClient->put(
                sprintf('document/%s', $document->getId()),
                json_encode(['data' =>
                    ['syncStatus' => Document::SYNC_STATUS_QUEUED]
                ])
            );
        }

        try {
            $content = $this->storage->retrieve($document->getStorageReference());
        } catch (S3Exception $e) {
            $syncStatus = in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES) ?
                Document::SYNC_STATUS_PERMANENT_ERROR : Document::SYNC_STATUS_TEMPORARY_ERROR;

            return $this->restClient->put(
                sprintf('document/%s', $document->getId()),
                json_encode(['data' => ['syncStatus' => $syncStatus, 'syncError' => 'S3 error: ' . $e->getMessage()]])
            );
        }

        try {
            /** @var ReportSubmission $latestSubmission */
            $latestSubmission = $report->getReportSubmissions()[0];
            $upload = $this->buildUpload($document);
            $apiGatewayResponse = $this->siriusApiGateWayClient->sendDocument($upload, $content, $report->getClient()->getCaseNumber());

            $data = json_decode(strval($apiGatewayResponse->getBody()), true);

            $this->restClient->put(
                sprintf('report-submission/%s', $latestSubmission->getId()),
                json_encode(['data' => ['uuid' => $data['data']['id']]])
            );

            return $this->restClient->put(
                sprintf('document/%s', $document->getId()),
                json_encode(['data' =>
                    ['syncStatus' => Document::SYNC_STATUS_SUCCESS]
                ])
            );
        } catch (RequestException $exception) {
            $body = $exception->getResponse() ? (string) $exception->getResponse()->getBody() : (string) $exception->getMessage();

            return $this->restClient->put(
                sprintf('document/%s', $document->getId()),
                json_encode(['data' =>
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => json_decode($body)]
                ])
            );
        }
    }

    private function buildUpload(Document $document)
    {
        $report = $document->getReport();

        $siriusDocumentMetadata = (new SiriusDocumentMetadata())
            ->setReportingPeriodFrom($report->getStartDate())
            ->setReportingPeriodTo($this->determineEndDate($report))
            ->setYear($report->getStartDate()->format('Y'))
            ->setDateSubmitted($report->getSubmitDate())
            ->setOrderType($this->determineReportType($report));

        return (new SiriusDocumentUpload())
            ->setType('reports')
            ->setAttributes($siriusDocumentMetadata);
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
}
