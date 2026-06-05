<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Mapper\ReportSatisfaction;

use OPG\Digideps\Frontend\Entity\Report\Satisfaction;
use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportSatisfactionSummaryMapperTest extends TestCase
{
    private ReportSatisfactionSummaryMapper $sut;
    private DateRangeQuery $query;
    private RestClient&MockObject $restClient;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new ReportSatisfactionSummaryMapper($this->restClient);
    }

    public function testReturnsReportSatisfactionsByDefaultParameters()
    {
        $this->query = new DateRangeQuery();

        $this->assertRestClientIsCalledWithDefaultQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->assertMapperReturnsResultFromRestClient();
    }

    public function testReturnsReportSatisfactionsByCustomParameters()
    {
        $this->query = new DateRangeQuery()
            ->setStartDate(new \DateTime('01-01-2001'))
            ->setEndDate(new \DateTime('02-01-2001'))
            ->setOrderBy('foo')
            ->setSortOrder('bar');

        $this->assertRestClientIsCalledWithCustomQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->assertMapperReturnsResultFromRestClient();
    }

    private function assertRestClientIsCalledWithDefaultQueryParameters(): void
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with('/satisfaction/satisfaction_data?orderBy=id&order=DESC', $this->anything())
            ->willReturn('returned-from-rest-client');
    }

    private function assertRestClientIsCalledWithCustomQueryParameters(): void
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildExpectedUrl(), $this->anything())
            ->willReturn(['returned-from-rest-client']);
    }

    private function buildExpectedUrl(): string
    {
        return sprintf('/satisfaction/satisfaction_data?%s', http_build_query([
            'orderBy' => $this->query->getOrderBy(),
            'order' => $this->query->getSortOrder(),
            'fromDate' => $this->query->getStartDate()->format('Y-m-d'),
            'toDate' => $this->query->getEndDate()->format('Y-m-d')
        ]));
    }

    private function assertRestClientPopulatesAnArrayOfExpectedEntities(): void
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with($this->anything(), Satisfaction::class . '[]');
    }

    private function assertMapperReturnsResultFromRestClient(): void
    {
        $this->assertEquals(['returned-from-rest-client'], $this->sut->getBy($this->query));
    }
}
