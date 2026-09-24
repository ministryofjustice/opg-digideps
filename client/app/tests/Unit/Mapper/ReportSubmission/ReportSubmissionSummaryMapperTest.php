<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Mapper\ReportSubmission;

use OPG\Digideps\Frontend\Entity\Report\ReportSubmissionSummary;
use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportSubmissionSummaryMapperTest extends TestCase
{
    private DateRangeQuery $query;
    private RestClient&MockObject $restClient;
    private mixed $result;
    private ReportSubmissionSummaryMapper $sut;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new ReportSubmissionSummaryMapper($this->restClient);
    }

    public function testReturnsReportSubmissionsByDefaultParameters()
    {
        $this->query = new DateRangeQuery();

        $this->assertRestClientIsCalledWithDefaultQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->result = $this->sut->getBy($this->query);
        $this->assertMapperReturnsResultFromRestClient();
    }

    public function testReturnsReportSubmissionsByCustomParameters()
    {
        $this->query = new DateRangeQuery()
            ->setStartDate(new \DateTime('01-01-2001'))
            ->setEndDate(new \DateTime('02-01-2001'))
            ->setOrderBy('foo')
            ->setSortOrder('bar');

        $this->assertRestClientIsCalledWithCustomQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->result = $this->sut->getBy($this->query);
        $this->assertMapperReturnsResultFromRestClient();
    }

    private function assertRestClientIsCalledWithDefaultQueryParameters(): void
    {
        $this->restClient->expects(self::once())
            ->method('get')
            ->with('/report-submission/pre-registration-data?orderBy=id&order=DESC', self::anything())
            ->willReturn('returned-from-rest-client');
    }

    private function assertRestClientIsCalledWithCustomQueryParameters()
    {
        $this->restClient->expects(self::once())
            ->method('get')
            ->with($this->buildExpectedUrl(), self::anything())
            ->willReturn('returned-from-rest-client');
    }

    private function buildExpectedUrl(): string
    {
        return sprintf('/report-submission/pre-registration-data?%s', http_build_query([
            'orderBy' => $this->query->getOrderBy(),
            'order' => $this->query->getSortOrder(),
            'fromDate' => '2001-01-01',
            'toDate' => '2001-01-02',
        ]));
    }

    private function assertRestClientPopulatesAnArrayOfExpectedEntities(): void
    {
        $this->restClient->expects(self::once())
            ->method('get')
            ->with(self::anything(), ReportSubmissionSummary::class . '[]');
    }

    private function assertMapperReturnsResultFromRestClient(): void
    {
        self::assertEquals('returned-from-rest-client', $this->result);
    }

}
