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
    private function saveCandidates(array $candidates, int $numDeputyships, int $numCandidates): void
    {
        foreach ($candidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();
        $this->em->clear();

        unset($candidates);

        if (0 === $numDeputyships % 10000) {
            $this->logger->info("Deputyship ingest progress: deputyships = $numDeputyships; candidates = $numCandidates");
        }
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
            $this->saveCandidates($candidates, $numDeputyships, $numCandidates);
        }

        try {
            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleReportCandidates();
            foreach ($candidates as $candidate) {
                ++$numCandidates;
                $this->saveCandidates([$candidate], $numDeputyships, $numCandidates);
            }

            $candidates = $this->courtOrderReportsCandidateFactory->createCompatibleNdrCandidates();
            foreach ($candidates as $candidate) {
                ++$numCandidates;
                $this->saveCandidates([$candidate], $numDeputyships, $numCandidates);
            }
        } catch (Exception $e) {
            $this->logger->error("ERROR while selecting candidates from deputyships: {$e->getMessage()}");

            return new DeputyshipCandidatesSelectorResult(new \ArrayIterator([]), 0, $e);
        }

        $this->logger->info(
            "Deputyship ingest progress - CANDIDATE SELECTION COMPLETE: \n".
            "deputyships = $numDeputyships; candidates = $numCandidates"
        );

        $candidatesResultset = $this->stagingSelectedCandidateRepository->getDistinctOrderedCandidates();

        return new DeputyshipCandidatesSelectorResult($candidatesResultset, $numCandidates);
    }
}
