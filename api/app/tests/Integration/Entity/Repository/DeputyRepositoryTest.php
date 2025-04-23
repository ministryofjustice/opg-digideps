<?php

namespace App\Repository;

use App\Entity\Deputy;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Integration\Fixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeputyRepositoryTest extends WebTestCase
{
    private DeputyRepository $sut;
    private EntityManagerInterface $em;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->fixtures = new Fixtures($this->em);

        $this->sut = $this->em->getRepository(Deputy::class);

        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    public function testFindReportsInfoByUid()
    {
        $faker = Factory::create('en_GB');

        $clientHelper = new ClientTestHelper();
        $client = $clientHelper->generateClient($this->em);
        
        $reportHelper = new ReportTestHelper();
        $report = $reportHelper->generateReport($this->em);
        
        $courtOrderHelper = new CourtOrderHelper();
    }
}
