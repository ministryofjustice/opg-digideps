<?php

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportRepositoryTest extends WebTestCase
{
    /**
     * @var \Fixtures
     */
    private static $fixtures;
    private static $repo;

    public static function setUpBeforeClass()
    {
        $client = static::createClient(['environment' => 'test',
                                               'debug' => true, ]);

        $em = $client->getContainer()->get('em');
        self::$fixtures = new \Fixtures($em);
        self::$fixtures->deleteReportsData();

        $em->clear();

        self::$repo = self::$fixtures->getRepo('Report\Report'); /** @var self::$repo ReportRepository */
    }

    /**
     * @to-do tests never implemented
     */
    public function testAddDebtsToReportIfMissing()
    {
        $this->markTestSkipped();
    }
}
