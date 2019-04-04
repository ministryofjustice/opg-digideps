<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Service\CasrecService;
use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
    protected static $fixtures;

    /**
     * @var CasrecService
     */
    private $object = null;

    public static function setUpBeforeClass()
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test',
                                                             'debug'       => false,]);

        self::$em = self::$frameworkBundleClient->getContainer()->get('em');

        self::$fixtures = new Fixtures(self::$em);
    }

    public function setup()
    {
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->reportService = self::$frameworkBundleClient->getContainer()->get('opg_digideps.report_service');
        $this->validator = self::$frameworkBundleClient->getContainer()->get('validator');

        $this->object = new CasrecService(self::$em, $this->logger, $this->reportService, $this->validator);
        Fixtures::deleteReportsData(['document', 'casrec', 'deputy_case', 'report_submission', 'report', 'odr', 'dd_team', 'dd_user', 'client', 'report']);
        self::$em->clear();
    }

    public function testAddBulkAndCsv()
    {
        $u1 = self::$fixtures->createUser()
            ->setDeputyNo('DN1')
            ->setRegistrationDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'))
            ->setLastLoggedIn(\DateTime::createFromFormat('d/m/Y', '02/11/2017'))
        ;
        // create Client C! with two submitted report + one active report
        $c1 = self::$fixtures->createClient($u1)->setCaseNumber('1234567t');
        self::$fixtures->createReport($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '05/06/2016'));
        self::$fixtures->createReport($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '05/06/2017'));
        self::$fixtures->createReport($c1)->setSubmitted(false);
        self::$fixtures->createNdr($c1)->setSubmitted(true)->setSubmitDate(\DateTime::createFromFormat('d/m/Y', '04/06/2016'));
        self::$em->flush();
        self::$em->clear();

        // add two casrec entries, first of which matches, 2nd does not
        $ret = $this->object->addBulk([
            [
                'Case' => '1234567T',
                'Surname' => 'R1',
                'Deputy No' => 'DN1',
                'Dep Surname' => 'R2',
                'Dep Postcode' => 'SW1 aH3',
                'Typeofrep' => 'OPG102',
                'Corref' => 'L2',
                'custom1' => 'c1',
                'NDR' => 1,
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
                'NDR' => '',
            ],

        ]);
        $this->assertEmpty($ret['errors'], print_r($ret, 1));
        $this->assertEquals(2, $ret['added'], print_r($ret, 1));

        self::$em->clear();
        $records = self::$em->getRepository(CasRec::class)->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $records);
        $record1 = $records[0]; /* @var $record1 CasRec */
        $record2 = $records[1]; /* @var $record1 CasRec */

        $this->assertEquals('r1', $record1->getClientLastname());
        $this->assertEquals('r2', $record1->getDeputySurname());
        $this->assertEquals('1', $record1->getColumn('NDR'));
        $this->assertEquals('sw1ah3', $record1->getDeputyPostCode());
        $this->assertEquals('opg102', $record1->getTypeOfReport());
        $this->assertEquals('l2', $record1->getCorref());
    }

    public function tearDown()
    {
        m::close();
    }
}
