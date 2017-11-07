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
        $this->em = m::mock(EntityManager::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->reportService = m::mock(ReportService::class);
        $this->validator = m::mock(ValidatorInterface::class);

        $this->object = new CasrecService(self::$em, $this->logger,  $this->reportService, $this->validator);
        Fixtures::deleteReportsData(['dd_user', 'client']);
        self::$em->clear();
    }

    public function testAddBulk()
    {
        $this->marktestSkipped('already covered by CasRecControllerTest');
    }

    public function tearDown()
    {
        m::close();
    }
}
