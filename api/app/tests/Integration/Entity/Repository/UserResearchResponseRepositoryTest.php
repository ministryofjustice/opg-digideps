<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use DateTime;
use App\Entity\UserResearch\UserResearchResponse;
use App\Repository\UserResearchResponseRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;

/** TODO - Refactor to move data generation out of the integration tests, DDLS-955 */
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

    public function testToAvoidWarning()
    {
        // PHPUnit 10 will fail the test suite if the class performs no tests
        $this->expectNotToPerformAssertions();
    }

    // Not to be run in test suites as it takes forever - run this to generate large amounts of userResearchResponses
    // for manual testing
    public function canHandleLargeAmountsOfData()
    {
        $this->fixtures->createUserResearchResponse(2000);
        $this->sut->getAllFilteredByDate(new DateTime('-1 day'), new DateTime());
    }
}
