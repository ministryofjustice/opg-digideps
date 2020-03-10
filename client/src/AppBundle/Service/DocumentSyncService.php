<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
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

    public function __construct(S3Storage $storage, SiriusApiGatewayClient $client)
    {
        $this->storage = $storage;
        $this->client = new Client([
            'base_uri' => 'http://pact-mock'
        ]);
    }

    public function syncReportDocument(Document $document)
    {
        $contents = $this->storage->retrieve($document->getStorageReference());

        $report = $document->getReport();
        $attributes = $this->getAttributesFromReport($report);

        try {
            $response = $this->sendReportDocument($report->getClient()->getCaseNumber(), $contents, $attributes);

            $data = json_decode(strval($response->getBody()), true);
            return $data['data']['id'];
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

    /**
     * @param Report|Ndr $report
     * @return array
     */
    private function getAttributesFromReport(ReportInterface $report): array
    {
        if ($report instanceof Ndr) {
            $type = 'NDR';
        } else if (in_array($report->getType(), [Report::TYPE_HEALTH_WELFARE, Report::TYPE_COMBINED_HIGH_ASSETS, Report::TYPE_COMBINED_LOW_ASSETS])) {
            $type = 'HW';
        } else {
            $type = 'PF';
        }

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
