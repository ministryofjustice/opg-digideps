<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Repository;

use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;
use OPG\Digideps\Backend\Repository\StagingSelectedCandidateRepository;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class StagingSelectedCandidateRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static StagingSelectedCandidateRepository $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var StagingSelectedCandidateRepository $sut */
        $sut = self::$entityManager->getRepository(StagingSelectedCandidate::class);
        self::$sut = $sut;
    }

    public function testGetDistinctOrderedCandidates(): void
    {
        // add two insert order candidates for the same court order UID
        self::$fixtures->createSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '12121212');
        self::$fixtures->createSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '12121212');

        // add another insert order candidate for a different UID
        self::$fixtures->createSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '99999999');

        $result = iterator_to_array(self::$sut->getDistinctOrderedCandidates());

        // ensure that only one of the two duplicate candidates is returned
        self::assertCount(2, $result);

        // the results should be ordered by court order UID
        self::assertTrue($result[1]['orderUid'] > $result[0]['orderUid']);
    }
}
