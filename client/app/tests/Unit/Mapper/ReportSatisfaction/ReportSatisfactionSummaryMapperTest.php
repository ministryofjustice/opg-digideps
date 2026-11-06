<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Mapper\ReportSatisfaction;

use OPG\Digideps\Frontend\Entity\Report\Satisfaction;
use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\TestCase;

class ReportSatisfactionSummaryMapperTest extends TestCase
{
    public function testReturnsReportSatisfactionsByDefaultParameters(): void
    {
        $restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new ReportSatisfactionSummaryMapper($restClient);
        $query = new DateRangeQuery();

        $restClient->expects(self::once())
            ->method('get')
            ->with('/satisfaction/satisfaction_data?orderBy=id&order=DESC', Satisfaction::class . '[]')
            ->willReturn(['returned-from-rest-client']);
        self::assertEquals(['returned-from-rest-client'], $sut->getBy($query));
    }

    public function testReturnsReportSatisfactionsByCustomParameters(): void
    {
        $restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new ReportSatisfactionSummaryMapper($restClient);
        $query = new DateRangeQuery()
            ->setStartDate(new \DateTime('01-01-2001'))
            ->setEndDate(new \DateTime('02-01-2001'))
            ->setOrderBy('foo')
            ->setSortOrder('bar');

        $url =  sprintf('/satisfaction/satisfaction_data?%s', http_build_query([
            'orderBy' => $query->getOrderBy(),
            'order' => $query->getSortOrder(),
            'fromDate' => $query->getStartDate()->format('Y-m-d'),
            'toDate' => $query->getEndDate()->format('Y-m-d')
        ]));

        $restClient->expects(self::once())
            ->method('get')
            ->with($url, Satisfaction::class . '[]')
            ->willReturn(['returned-from-rest-client']);
        self::assertEquals(['returned-from-rest-client'], $sut->getBy($query));
    }
}
