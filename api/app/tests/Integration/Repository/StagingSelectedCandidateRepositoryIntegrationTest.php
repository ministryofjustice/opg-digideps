<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Repository;

use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;
use OPG\Digideps\Backend\Repository\StagingSelectedCandidateRepository;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class StagingSelectedCandidateRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private static StagingSelectedCandidateRepository $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var StagingSelectedCandidateRepository $sut */
        $sut = self::$entityManager->getRepository(StagingSelectedCandidate::class);
        self::$sut = $sut;
    }

    public function testGetDistinctOrderedCandidates(): void
    {
        $result = self::$sut->getDistinctOrderedCandidates();
        self::assertNotEmpty(iterator_to_array($result));
    }
}
