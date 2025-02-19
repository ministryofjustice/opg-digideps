<?php

namespace App\Tests\Integration\Service\Stats\Query;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Service\Stats\Query\ClientsQuery;
use App\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientsQueryTest extends WebTestCase
{
    /** @var EntityManager */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        self::$em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        static::givenClientWithReportsOfType(['NDR', '102']);
        static::givenClientWithReportsOfType(['NDR', '102']);
        static::givenClientWithReportsOfType(['102', '102']);
        static::givenClientWithReportsOfType(['103']);
        static::givenClientWithReportsOfType(['103']);
        static::givenClientWithReportsOfType(['102-5']);
        static::givenClientWithReportsOfType(['102-5']);
        static::givenClientWithReportsOfType(['103-5']);
        static::givenClientWithReportsOfType(['102-6']);
        static::givenClientWithReportsOfType(['102-6']);
        static::givenClientWithReportsOfType(['103-6']);

        self::$em->flush();
    }

    public function testReturnsTotalClientsByDeputyType()
    {
        $query = new ClientsQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'clients',
            'dimension' => ['deputyType'],
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(5, $metric['amount']);
                    break;
                case 'pa':
                    $this->assertEquals(3, $metric['amount']);
                    break;
                case 'prof':
                    $this->assertEquals(3, $metric['amount']);
                    break;
            }
        }
    }

    public function testReturnsTotalClientByReportType()
    {
        $query = new ClientsQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'clients',
            'dimension' => ['reportType'],
        ]));

        // Assert an array result for each report type submitted
        $this->assertCount(7, $result);

        // Assert correct amount is returned for each report type
        foreach ($result as $metric) {
            switch ($metric['reportType']) {
                case '102':
                    $this->assertEquals(3, $metric['amount']);
                    break;
                case '103':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '102-6':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '103-6':
                    $this->assertEquals(1, $metric['amount']);
                    break;
                case '102-5':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '103-5':
                    $this->assertEquals(1, $metric['amount']);
                    break;
            }
        }
    }

    private static function givenClientWithReportsOfType(array $reportTypes)
    {
        $client = new Client();
        foreach ($reportTypes as $reportType) {
            if ('NDR' === $reportType) {
                $report = new Ndr($client);
            } else {
                $report = new Report(
                    $client,
                    $reportType,
                    new \DateTime('2019-08-01'),
                    new \DateTime('2020-08-01')
                );
            }

            self::$em->persist($report);
        }

        self::$em->persist($client);
    }
}
