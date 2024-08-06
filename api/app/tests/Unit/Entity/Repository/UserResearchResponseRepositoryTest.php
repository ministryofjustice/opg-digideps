<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Repository;

use App\Entity\UserResearch\UserResearchResponse;
use App\Repository\UserResearchResponseRepository;
use App\Service\ReportService;
use App\Tests\Unit\Fixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserResearchResponseRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Fixtures $fixtures;
    private UserResearchResponseRepository $sut;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $reportService = $kernel->getContainer()->get(ReportService::class);

        $this->fixtures = new Fixtures($this->em, $reportService);

        $this->sut = $this->em->getRepository(UserResearchResponse::class);
    }

    // Not to be run in test suites as it takes forever - run this to generate large amounts of userResearchResponses
    // for manual testing
    public function canHandleLargeAmountsOfData()
    {
        $this->fixtures->createUserResearchResponse(2000);
        $urs = $this->sut->getAllFilteredByDate(new \DateTime('-1 day'), new \DateTime());
    }
}
