<?php

namespace AppBundle\Mapper\ReportSubmission;

use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryQuery;
use AppBundle\Service\Client\RestClient;
use PHPUnit\Framework\TestCase;

class ReportSubmissionSummaryMapperTest extends TestCase
{
    /** @var ReportSubmissionSummaryMapper */
    private $sut;

    /** @var ReportSubmissionSummaryQuery */
    private $query;

    /** @var RestClient | \PHPUnit_Framework_MockObject_MockObject */
    private $restClient;

    /** @var mixed */
    private $result;

    public function setUp()
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new ReportSubmissionSummaryMapper($this->restClient);
    }

    public function testReturnsReportSubmissionsByDefaultParameters()
    {
        $this->query = new ReportSubmissionSummaryQuery();

        $this->assertRestClientIsCalledWithDefaultQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->invokeMapper();
        $this->assertMapperReturnsResultFromRestClient();
    }

    public function testReturnsReportSubmissionsByCustomParameters()
    {
        $this->query = (new ReportSubmissionSummaryQuery())
            ->setFromDate(new \DateTime('01-01-2001'))
            ->setToDate(new \DateTime('02-01-2001'))
            ->setOrderBy('foo')
            ->setSortOrder('bar');

        $this->assertRestClientIsCalledWithCustomQueryParameters();
        $this->assertRestClientPopulatesAnArrayOfExpectedEntities();
        $this->invokeMapper();
        $this->assertMapperReturnsResultFromRestClient();
    }

    private function assertRestClientIsCalledWithDefaultQueryParameters()
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with('/report-submission/casrec_data?orderBy=id&order=DESC', $this->anything())
            ->willReturn('returned-from-rest-client');
    }

    private function assertRestClientIsCalledWithCustomQueryParameters()
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildExpectedUrl(), $this->anything())
            ->willReturn('returned-from-rest-client');
    }

    /**
     * @return string
     */
    private function buildExpectedUrl()
    {
        return sprintf('/report-submission/casrec_data?%s', http_build_query([
            'fromDate' => $this->query->getStartDate(),
            'toDate' => $this->query->getEndDate(),
            'orderBy' => $this->query->getOrderBy(),
            'order' => $this->query->getSortOrder()
        ]));
    }

    private function assertRestClientPopulatesAnArrayOfExpectedEntities()
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with($this->anything(), 'Report\ReportSubmissionSummary[]');
    }

    private function assertMapperReturnsResultFromRestClient()
    {
        $this->assertEquals($this->result, 'returned-from-rest-client');
    }

    private function invokeMapper()
    {
        $this->result = $this->sut->getBy($this->query);
    }
}
