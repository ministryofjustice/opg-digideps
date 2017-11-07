<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\CasrecService;
use AppBundle\Service\PaService;
use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CasrecServiceTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;


    /**
     * @var EntityManager
     */
    protected static $em;

    /**
     * @var Fixtures
     */
//    protected static $fixtures;

    /**
     * @var CasrecService
     */
    private $object = null;

    public static function setUpBeforeClass()
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');
    }

    public function setup()
    {
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
//        $this->reportService = m::mock(ReportService::class)->shouldIgnoreMissing;
        $this->reportService = self::$frameworkBundleClient->getContainer()->get('opg_digideps.report_service');
        $this->validator = self::$frameworkBundleClient->getContainer()->get('validator');

        $this->object = new CasrecService(self::$em, $this->logger,  $this->reportService, $this->validator);
        Fixtures::deleteReportsData(['dd_user', 'client']);
        self::$em->clear();
    }

    public function testAddBulk()
    {
        $ret = $this->object->addBulk([
            [
                'Case' => '11',
                'Surname' => 'R1',
                'Deputy No' => 'DN1',
                'Dep Surname' => 'R2',
                'Dep Postcode' => 'SW1 aH3',
                'Typeofrep' => 'OPG102',
                'Corref' => 'L2',
                'custom1' => 'c1',
            ],
            [
                'Case' => '22',
                'Surname' => 'H1',
                'Deputy No' => 'DN2',
                'Dep Surname' => 'H2',
                'Dep Postcode' => '',
                'Typeofrep' => 'OPG103',
                'Corref' => 'L3',
                'custom 2' => 'c2',
            ],

        ]);
        $this->assertEmpty($ret['errors'], print_r($ret, 1));
        $this->assertEquals(2, $ret['added'], print_r($ret, 1));

        self::$em->clear();
        $records = self::$em->getRepository(EntityDir\CasRec::class)->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $records);
        $record1 = $records[0]; /* @var $record1 CasRec */
        $record2 = $records[1]; /* @var $record2 CasRec */

        $this->assertEquals('11', $record1->getCaseNumber());
        $this->assertEquals('r1', $record1->getClientLastname());
        $this->assertEquals('dn1', $record1->getDeputyNo());
        $this->assertEquals('r2', $record1->getDeputySurname());
        $this->assertEquals('sw1ah3', $record1->getDeputyPostCode());
        $this->assertEquals('opg102', $record1->getTypeOfReport());
        $this->assertEquals('l2', $record1->getCorref());
        $this->assertEquals('c1', $record1->getOtherColumns()['custom1']);

        $this->assertEquals('22', $record2->getCaseNumber());
        $this->assertEquals('c2', $record2->getOtherColumns()['custom 2']);
    }

    public function tearDown()
    {
        m::close();
    }
}
