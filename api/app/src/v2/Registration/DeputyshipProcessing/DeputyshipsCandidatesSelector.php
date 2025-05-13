<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Repository\StagingDeputyshipRepository;
use App\Repository\StagingSelectedCandidateRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StagingDeputyshipRepository $stagingDeputyshipRepository,
        private readonly CourtOrderAndDeputyCandidatesFactory $courtOrderAndDeputyCandidatesFactory,
        private readonly CourtOrderReportCandidatesFactory $courtOrderReportsCandidateFactory,
        private readonly StagingSelectedCandidateRepository $stagingSelectedCandidateRepository,
        private readonly LoggerInterface $logger,
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
        $this->em->clear();

        unset($candidates);
    }

    public function select(): DeputyshipCandidatesSelectorResult
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidate sc')->execute();
        $this->em->flush();
        $this->em->commit();

        $this->courtOrderAndDeputyCandidatesFactory->cacheLookupTables();

        $numCandidates = 0;
        $numDeputyships = 0;

        // read the content of the incoming deputyships CSV from the db table
        $csvDeputyships = $this->stagingDeputyshipRepository->findAllPaged();

        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            ++$numDeputyships;

            $candidates = $this->courtOrderAndDeputyCandidatesFactory->create($csvDeputyship);
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);

            if (0 === $numDeputyships % 10000) {
                error_log("Deputyship ingest progress: deputyships = $numDeputyships; candidates = $numCandidates");
            }
        }

        error_log("Deputyship ingest progress: deputyships = $numDeputyships; candidates  = $numCandidates");

        try {
            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleReportCandidates();
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);

            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleNdrCandidates();
            $numCandidates += count($candidates);
            $this->saveCandidates($candidates);
        } catch (Exception $e) {
            $this->logger->error("ERROR while selecting candidates from deputyships: {$e->getMessage()}");

            return new DeputyshipCandidatesSelectorResult([], 0, $e);
        }

        $candidatesResultset = $this->stagingSelectedCandidateRepository->getDistinctCandidates();

        return new DeputyshipCandidatesSelectorResult($candidatesResultset, $numCandidates);
    }
}
