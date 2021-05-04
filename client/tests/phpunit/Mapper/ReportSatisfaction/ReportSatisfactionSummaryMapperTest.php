<?php declare(strict_types=1);

namespace App\Mapper\ReportSatisfaction;

use App\Mapper\DateRangeQuery;
use App\Service\Client\RestClient;
use PHPUnit\Framework\TestCase;

class ReportSatisfactionSummaryMapperTest extends TestCase
{
    /** @var ReportSatisfactionSummaryMapper */
    private $sut;

    /** @var DateRangeQuery */
    private $query;

    /** @var RestClient | \PHPUnit_Framework_MockObject_MockObject */
    private $restClient;

    /** @var mixed */
    private $result;

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
        $this->invokeMapper();
        $this->assertMapperReturnsResultFromRestClient();
    }

    public function testReturnsReportSatisfactionsByCustomParameters()
    {
        $this->query = (new DateRangeQuery())
            ->setStartDate(new \DateTime('01-01-2001'))
            ->setEndDate(new \DateTime('02-01-2001'))
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
            ->with('/satisfaction/satisfaction_data?orderBy=id&order=DESC', $this->anything())
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
        return sprintf('/satisfaction/satisfaction_data?%s', http_build_query([
            'orderBy' => $this->query->getOrderBy(),
            'order' => $this->query->getSortOrder(),
            'fromDate' => $this->query->getStartDate()->format('Y-m-d'),
            'toDate' => $this->query->getEndDate()->format('Y-m-d')
        ]));
    }

    private function assertRestClientPopulatesAnArrayOfExpectedEntities()
    {
        $this
            ->restClient
            ->expects($this->once())
            ->method('get')
            ->with($this->anything(), 'Report\Satisfaction[]');
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
