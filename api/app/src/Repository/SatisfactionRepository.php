<?php

namespace App\Repository;

use App\Entity\Satisfaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SatisfactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Satisfaction::class);
    }

    public function findAllSatisfactionSubmissions(
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null
    ): array {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT s.id, s.score, s.comments, s.deputyrole, s.reporttype, s.created
             FROM App\Entity\Satisfaction s
             WHERE (s.report IS NOT NULL OR s.ndr IS NOT NULL)
             AND s.created > :fromDate
             AND s.created < :toDate'
        )->setParameters(['fromDate' => $fromDate, 'toDate' => $toDate]);

        return $query->getResult();
    }

    public function getSatisfactionDataForPeriod(\DateTime $statsStartDate, \DateTime $statsEndDate): array
    {
        $satisfactionScoresQuery = '
            SELECT
                count(CASE WHEN score = 1 THEN 1 END) AS very_dissatisfied,
                count(CASE WHEN score = 2 THEN 1 END) AS dissatisfied,
                count(CASE WHEN score = 3 THEN 1 END) AS neither,
                count(CASE WHEN score = 4 THEN 1 END) AS satisfied,
                count(CASE WHEN score = 5 THEN 1 END) AS very_satisfied
            FROM satisfaction
            WHERE (report_id IS NOT NULL OR ndr_id IS NOT NULL)
            AND created_at >= :fromDate
            AND created_at <= :toDate
        ';

        $conn = $this->getEntityManager()->getConnection();
        $statsStmt = $conn->prepare($satisfactionScoresQuery);
        $statsStmt->bindValue('fromDate', $statsStartDate->format('Y-m-d H:i:s'));
        $statsStmt->bindValue('toDate', $statsEndDate->format('Y-m-d H:i:s'));
        $result = $statsStmt->executeQuery()->fetchAllAssociative();

        if (count($result) < 1) {
            return [];
        }

        $satisfactionScoresResults = $result[0];

        // calculate percentage from satisfied and very satisfied counts
        $numSatisfied = $satisfactionScoresResults['satisfied'] + $satisfactionScoresResults['very_satisfied'];
        $total = array_sum($satisfactionScoresResults);

        if (0 === $total) {
            return [];
        }

        $satisfactionScoresResults['user_satisfaction_percent'] = round(($numSatisfied / $total) * 100);

        $statsStartDateStr = $statsStartDate->format('Y-m-d');
        $satisfactionScores = [];
        foreach ($satisfactionScoresResults as $satisfactionScoreKey => $satisfactionScoreRow) {
            $satisfactionScores[] = [
                '_timestamp' => $statsStartDateStr . 'T00:00:00+00:00',
                'service' => 'deputy-reporting',
                'channel' => 'digital',
                'count' => intval($satisfactionScoreRow),
                'dataType' => str_replace('_', '-', $satisfactionScoreKey),
                'period' => 'month',
            ];
        }

        return $satisfactionScores;
    }
}
