<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Mapper\ReportSubmission;

use OPG\Digideps\Frontend\Entity\Report\ReportSubmissionSummary;
use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\TestCase;

class ReportSubmissionSummaryMapperTest extends TestCase
{
    public function testReturnsReportSubmissionsByDefaultParameters()
    {
        $restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new ReportSubmissionSummaryMapper($restClient);
        $query = new DateRangeQuery();

        $restClient->expects(self::once())
            ->method('get')
            ->with('/report-submission/pre-registration-data?orderBy=id&order=DESC', ReportSubmissionSummary::class . '[]')
            ->willReturn('returned-from-rest-client');
        $result = $sut->getBy($query);
        self::assertEquals('returned-from-rest-client', $result);
    }

    public function testReturnsReportSubmissionsByCustomParameters()
    {
        $restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new ReportSubmissionSummaryMapper($restClient);
        $query = new DateRangeQuery()
            ->setStartDate(new \DateTime('01-01-2001'))
            ->setEndDate(new \DateTime('02-01-2001'))
            ->setOrderBy('foo')
            ->setSortOrder('bar');

        $url = sprintf('/report-submission/pre-registration-data?%s', http_build_query([
            'orderBy' => $query->getOrderBy(),
            'order' => $query->getSortOrder(),
            'fromDate' => '2001-01-01',
            'toDate' => '2001-01-02',
        ]));

        $restClient->expects(self::once())
            ->method('get')
            ->with($url, ReportSubmissionSummary::class . '[]')
            ->willReturn('returned-from-rest-client');
        $result = $sut->getBy($query);
        self::assertEquals('returned-from-rest-client', $result);
    }
}
