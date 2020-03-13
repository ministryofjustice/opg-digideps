<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
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
        $this->client = new Client([
            'base_uri' => 'http://pact-mock'
        ]);
        $this->siriusApiGateWayClient = $siriusApiGateWayClient;
        $this->restClient = $restClient;
    }

    public function syncReportDocument(Document $document)
    {
        $content = $this->storage->retrieve($document->getStorageReference());

        /** @var Report $report */
        $report = $document->getReport();

        /** @var ReportSubmission $latestSubmission */
        $latestSubmission = $report->getReportSubmissions()[0];
        $submissionId = $latestSubmission->getId();

        try {
            $upload = $this->buildUpload($document);
            $response = $this->siriusApiGateWayClient->sendDocument($upload, $content, $report->getClient()->getCaseNumber());

            $data = json_decode(strval($response->getBody()), true);

            $this->restClient->put(
                sprintf('report-submission/%s', $submissionId),
                json_encode(['data' => ['response' => json_encode($response->getBody())]])
            );
        } catch (RequestException $exception) {
            $body = $exception->getResponse() ? $exception->getResponse()->getBody() : json_encode($exception->getMessage());

            $this->restClient->put(
                sprintf('report-submission/%s', $submissionId),
                json_encode(['data' => ['response' => $body]])
            );
        }

        if ($data['data']['id'])  {
            return $data['data']['id'];
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
