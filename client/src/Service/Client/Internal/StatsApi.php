<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Service\Client\RestClientInterface;

class StatsApi
{
    protected const GET_ACTIVE_LAY_REPORT_DATA_ENDPOINT = 'stats/deputies/lay/active';
    protected const GET_ADMIN_USER_ACCOUNT_REPORT_DATA = 'stats/admins/report_data';
    protected const GET_ASSETS_TOTAL_VALUES = 'stats/assets/total_values';
    protected const GET_BENEFITS_REPORT_METRICS = 'stats/report/benefits-report-metrics';

    private RestClientInterface $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    public function getActiveLayReportData(): array
    {
        return $this->restClient->get(
            self::GET_ACTIVE_LAY_REPORT_DATA_ENDPOINT,
            'array',
            ['active-users']
        );
    }

    public function getAdminUserAccountReportData(): array
    {
        return $this->restClient->get(
            self::GET_ADMIN_USER_ACCOUNT_REPORT_DATA,
            'array',
            ['admin-account-reports']
        );
    }

    public function getAssetsTotalValuesWithin12Months(): string
    {
        $response = $this->restClient->get(
            self::GET_ASSETS_TOTAL_VALUES,
            'raw',
            ['admin-account-reports']
        );

        return (string) $response;
    }

    public function getBenefitsReportMetrics(): array
    {
        return $this->restClient->get(
            self::GET_BENEFITS_REPORT_METRICS,
            'array',
            ['benefits-report-metrics']
        );
    }
}
