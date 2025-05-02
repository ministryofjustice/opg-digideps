<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Repository\StagingDeputyshipRepository;
use App\Repository\StagingSelectedCandidateRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StagingDeputyshipRepository $stagingDeputyshipRepository,
        private readonly CourtOrderAndDeputyCandidatesFactory $courtOrderAndDeputyCandidatesFactory,
        private readonly CourtOrderReportCandidatesFactory $courtOrderReportsCandidateFactory,
        private readonly StagingSelectedCandidateRepository $stagingSelectedCandidateRepository,
    ) {
    }

    /**
     * @param StagingSelectedCandidate[] $candidates
     */
    private function saveCandidates(array $candidates): void
    {
        foreach ($candidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();
    }

    public function select(): DeputyshipCandidatesSelectorResult
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidate sc')->execute();
        $this->em->flush();
        $this->em->commit();

        // read the content of the incoming deputyships CSV from the db table
        $csvDeputyships = $this->stagingDeputyshipRepository->findAll();

        $this->courtOrderAndDeputyCandidatesFactory->cacheLookupTables();

        $numCandidates = 0;

        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            $candidates = $this->courtOrderAndDeputyCandidatesFactory->create($csvDeputyship);
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);
        }

        try {
            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleReportCandidates();
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);

            $candidates = $this->courtOrderReportsCandidateFactory->createNewReportCandidates();
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);

            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleNdrCandidates();
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);
        } catch (Exception $e) {
            return new DeputyshipCandidatesSelectorResult([], 0, $e);
        }

        $candidatesResultset = $this->stagingSelectedCandidateRepository->getDistinctCandidates();

        return new DeputyshipCandidatesSelectorResult($candidatesResultset, $numCandidates);
    }
}
