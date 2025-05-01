<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Repository\StagingDeputyshipRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StagingDeputyshipRepository $stagingDeputyshipRepository,
        private readonly CourtOrderAndDeputyCandidatesFactory $courtOrderAndDeputyCandidatesFactory,
        private readonly CourtOrderReportCandidatesFactory $courtOrderReportsCandidateFactory,
    ) {
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

        $candidates = [];

        $this->courtOrderAndDeputyCandidatesFactory->cacheLookupTables();

        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            $candidates = array_merge($candidates, $this->courtOrderAndDeputyCandidatesFactory->create($csvDeputyship));
        }

        try {
            $candidates = array_merge($candidates, $this->courtOrderReportsCandidateFactory->createCompatibleReportCandidates());
            $candidates = array_merge($candidates, $this->courtOrderReportsCandidateFactory->createIncompatibleReportCandidates());
        } catch (Exception $e) {
            return new DeputyshipCandidatesSelectorResult([], $e);
        }

        foreach ($candidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();

        return new DeputyshipCandidatesSelectorResult($candidates);
    }
}
