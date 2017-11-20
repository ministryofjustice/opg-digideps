<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Service\CasrecService;
use AppBundle\Service\PaService;
use AppBundle\Service\ReportService;
use Doctrine\ORM\EntityManager;
use Fixtures;
use Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\DateTime;
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

        $this->object = new CasrecService(self::$em, $this->logger,  $this->reportService, $this->validator);
        Fixtures::deleteReportsData(['dd_user', 'client', 'report']);
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
        $records = self::$em->getRepository(CasRec::class)->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $records);
        $record1 = $records[0]; /* @var $record1 CasRec */
        $record2 = $records[1]; /* @var $record1 CasRec */

        $this->assertEquals('r1', $record1->getClientLastname());
        $this->assertEquals('r2', $record1->getDeputySurname());
        $this->assertEquals('sw1ah3', $record1->getDeputyPostCode());
        $this->assertEquals('opg102', $record1->getTypeOfReport());
        $this->assertEquals('l2', $record1->getCorref());

        //check stats
        $casrecArray = $record1->toArray();
        $this->assertContains(date('d/m/Y'), $casrecArray['Uploaded at']);
        $this->assertContains(date('d/m/Y'), $casrecArray['Stats updated at']);
        $this->assertContains('01/11/2017', $casrecArray['Deputy registration date']);
        $this->assertContains('02/11/2017', $casrecArray['Deputy last logged in']);
        $this->assertEquals(2, $casrecArray['Reports submitted']);
        $this->assertContains('05/06/2017', $casrecArray['Last report submitted at']);
        $this->assertEquals(1, $casrecArray['Reports active']);
        $this->assertContains('c1', $casrecArray['custom1']); // custom data is kepy
        $this->assertContains('DN1', $casrecArray['Deputy No']);
        $this->assertContains('1234567T', $casrecArray['Case']);

        $casrecArray = $record2->toArray();
        $this->assertContains(date('d/m/Y'), $casrecArray['Uploaded at']);
        $this->assertContains(date('d/m/Y'), $casrecArray['Stats updated at']);
        $this->assertContains('n.a.', $casrecArray['Deputy registration date']);
        $this->assertContains('n.a.', $casrecArray['Deputy last logged in']);
        $this->assertEquals('n.a.', $casrecArray['Reports submitted']);
        $this->assertEquals('n.a.', $casrecArray['Last report submitted at']);
        $this->assertEquals('n.a.', $casrecArray['Reports active']);

        // test CSV
        $file ='/tmp/dd_stats.unittest.csv';
        $this->object->saveCsv($file);
        $this->assertCount(3, file($file));
    }

    public function tearDown()
    {
        m::close();
    }
}
