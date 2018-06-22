<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var Client $client */
    private $client;

    protected function setUp()
    {
        $this->client = new Client();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Data providor, expected reporting start date from a given court date
     */
    public function courtDateExpectedStartDateProvider()
    {
        $currentYear = date('Y');
        return [
            [new \DateTime('2000-01-01'), new \DateTime($currentYear . '-01-01')],
            [new \DateTime('2000-12-31'), new \DateTime(($currentYear -1) . '-12-31')],
        ];

    }

    /**
     * Data providor, expected reporting end date from a given court date
     */
    public function courtDateExpectedEndDateProvider()
    {
        $currentYear = date('Y');

        return [
            [new \DateTime('2000-01-01'), new \DateTime($currentYear . '-12-31')],
            [new \DateTime('2000-12-31'), new \DateTime($currentYear . '-12-30')],
        ];

    }

    /**
     * @dataProvider courtDateExpectedStartDateProvider
     */
    public function testGetExpectedStartDate($courtDate, $expected)
    {
        $this->client->setCourtDate($courtDate);
        $this->assertEquals(
            $expected->format('d/m/Y'),
            $this->client->getExpectedReportStartDate()->format('d/m/Y')
        );
    }


    /**
     * @dataProvider courtDateExpectedEndDateProvider
     */
    public function testGetExpectedEndDate($courtDate, $expected)
    {
        $this->client->setCourtDate($courtDate);
        $this->assertEquals(
            $expected->format('d/m/Y'),
            $this->client->getExpectedReportEndDate()->format('d/m/Y')
        );
    }



}
