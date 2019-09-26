<?php

namespace Tests\AppBundle\Service\Stats\Metrics;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\Stats\Metrics\MetricReportsSubmittedQuery;
use AppBundle\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MetricReportsSubmittedQueryTest extends WebTestCase
{
    /** @var EntityManager */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);
        self::$em = $frameworkBundleClient->getContainer()->get('em');

        static::givenXreportSubmissionsOfTypeBelongToDeputy('4', '102', 'LAY');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '102', 'LAY');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '103', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('1', '102-6', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '103-6', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('4', '102-5', 'PROF');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('3', '103-5', 'PROF');

        self::$em->flush();
    }

    public function testReturnsTotalReportsSubmittedByDeputyType()
    {
        $query = new MetricReportsSubmittedQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'reportsSubmitted',
            'dimension' => ['deputyType']
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(6, $metric['amount']);
                    break;
                case 'pa':
                    $this->assertEquals(5, $metric['amount']);
                    break;
                case 'prof':
                    $this->assertEquals(7, $metric['amount']);
                    break;
            }
        }
    }

    public function testReturnsTotalReportsSubmittedByReportType()
    {
        $query = new MetricReportsSubmittedQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'reportsSubmitted',
            'dimension' => ['reportType']
        ]));

        // Assert an array result for each report type submitted
        $this->assertCount(6, $result);

        // Assert correct amount is returned for each report type
        foreach ($result as $metric) {
            switch ($metric['reportType']) {
                case '102':
                    $this->assertEquals(6, $metric['amount']);
                    break;
                case '103':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '102-6':
                    $this->assertEquals(1, $metric['amount']);
                    break;
                case '103-6':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '102-5':
                    $this->assertEquals(4, $metric['amount']);
                    break;
                case '103-5':
                    $this->assertEquals(3, $metric['amount']);
                    break;
            }
        }
    }

    /**
     * @param $numReports
     * @param $reportType
     * @param $deputyType
     * @throws \Exception
     */
    private static function givenXreportSubmissionsOfTypeBelongToDeputy($numReports, $reportType, $deputyType)
    {
        for ($i = 0; $i < $numReports; $i++) {
            static::addSubmittedReportOfTypeToUser($reportType, static::createUserOfType($deputyType));
        }
    }

    /**
     * @param $type
     * @return User
     */
    private static function createUserOfType($type)
    {
        $id = mt_rand();
        $user = (new User())
            ->setFirstname('Lay')
            ->setEmail("metric-test-$id@publicguardian.gov.uk")
            ->setRoleName('ROLE_'.$type.'_DEPUTY');

        self::$em->persist($user);

        return $user;
    }

    /**
     * @param $type
     * @param User $user
     * @throws \Exception
     */
    private static function addSubmittedReportOfTypeToUser($type, User $user)
    {
        $client = new Client();

        $report = new Report(
            $client,
            $type,
            new \DateTime('2019-08-01'),
            new \DateTime('2020-08-01')
        );

        $submission = new ReportSubmission($report, $user);

        self::$em->persist($client);
        self::$em->persist($report);
        self::$em->persist($submission);
    }
}
