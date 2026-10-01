<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Repository;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;
use OPG\Digideps\Backend\Repository\StagingSelectedCandidateRepository;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class StagingSelectedCandidateRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private const string UID1 = '12121212';
    private const string UID2 = '99999999';

    public function testGetDistinctOrderedCandidates(): void
    {
        // add two insert order candidates for the same court order UID
        $this->makeSelectedCandidate(DeputyshipCandidateAction::InsertOrder, self::UID1);
        $this->makeSelectedCandidate(DeputyshipCandidateAction::InsertOrder, self::UID1);

        // add another insert order candidate for a different UID
        $this->makeSelectedCandidate(DeputyshipCandidateAction::InsertOrder, self::UID2);

        self::$entityManager->flush();

        /** @var StagingSelectedCandidateRepository $sut */
        $sut = self::$entityManager->getRepository(StagingSelectedCandidate::class);
        $result = iterator_to_array($sut->getDistinctOrderedCandidates());

        // ensure that only one of the two duplicate candidates is returned
        self::assertCount(2, $result);

        // the results should be ordered by court order UID
        self::assertTrue($result[1]['orderUid'] > $result[0]['orderUid']);
    }

    private function makeSelectedCandidate(
        DeputyshipCandidateAction $action,
        string $orderUid,
        CourtOrderKind $courtOrderKind = CourtOrderKind::Single,
        CourtOrderType $orderType = CourtOrderType::PFA,
        string $reportType = Report::LAY_PFA_LOW_ASSETS_TYPE,
        ?string $status = null,
        ?int $clientId = null,
        ?\DateTime $orderMadeDate = null
    ): StagingSelectedCandidate {
        $candidate = new StagingSelectedCandidate($action, $orderUid);
        $candidate->courtOrderKind = $courtOrderKind->value;
        $candidate->orderType = $orderType->value;
        $candidate->reportType = $reportType;
        $candidate->status = $status;
        $candidate->clientId = $clientId;
        $candidate->orderMadeDate = ($orderMadeDate ?: new \DateTime('now'))->format('Y-m-d');
        self::$entityManager->persist($candidate);
        return $candidate;
    }
}
