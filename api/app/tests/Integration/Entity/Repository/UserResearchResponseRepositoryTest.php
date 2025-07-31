<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\UserResearch\UserResearchResponse;
use App\Repository\UserResearchResponseRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;

class UserResearchResponseRepositoryTest extends ApiBaseTestCase
{
    private Fixtures $fixtures;
    private UserResearchResponseRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        /** @var UserResearchResponseRepository $sut */
        $sut = $this->entityManager->getRepository(UserResearchResponse::class);
        $this->sut = $sut;
    }

    // Not to be run in test suites as it takes forever - run this to generate large amounts of userResearchResponses
    // for manual testing
    public function canHandleLargeAmountsOfData()
    {
        $this->fixtures->createUserResearchResponse(2000);
        $this->sut->getAllFilteredByDate(new \DateTime('-1 day'), new \DateTime());
    }
}
