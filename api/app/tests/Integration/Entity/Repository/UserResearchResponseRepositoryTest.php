<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\UserResearch\UserResearchResponse;
use App\Repository\UserResearchResponseRepository;
use App\Tests\Integration\Fixtures;
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
        $this->fixtures = new Fixtures($this->em);

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
