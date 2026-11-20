<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Admin;

use OPG\Digideps\Common\Report\ReportReplacement;
use OPG\Digideps\Common\Report\ReportRestoreResponse;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use Psr\Http\Message\StreamInterface;

final readonly class ReportService
{
    public function __construct(
        private RestClient $restClient
    ) {
    }

    public function restoreReport(ReportReplacement $reportReplacement): ReportRestoreResponse
    {
        $requestBody = json_encode($reportReplacement);
        if ($requestBody === false) {
            throw new \RuntimeException('Failed to encode report restore request body');
        }

        try {
            /** @var StreamInterface $rawResponse */
            $rawResponse = $this->restClient->apiCall('post', 'report/restore', $requestBody, 'raw');

            $response = ReportRestoreResponse::jsonDeserialize((string)$rawResponse);
        } catch (\Throwable) {
            return new ReportRestoreResponse(false, 'Network error');
        }

        return $response;
    }
}
