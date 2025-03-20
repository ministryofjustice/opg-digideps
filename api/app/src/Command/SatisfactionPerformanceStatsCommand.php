<?php

namespace App\Command;

use App\Service\File\Storage\S3SatisfactionDataStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SatisfactionPerformanceStatsCommand extends Command
{
    public static $defaultName = 'digideps:satisfaction-performance-stats';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly S3SatisfactionDataStorage $s3SatisfactionDataStorage
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetches the satisfaction scores for Digideps and prepares the json data for update on the Opg Services Performance Data repo')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $satisfactionScoresQuery = "
                    SELECT
                        ROUND(AVG(score - 1) * 25) AS user_satisfaction_percent,
                        count(CASE WHEN score = 1 THEN 1 END) AS very_dissatisfied,
                        count(CASE WHEN score = 2 THEN 1 END) AS dissatisfied,
                        count(CASE WHEN score = 3 THEN 1 END) AS neither,
                        count(CASE WHEN score = 4 THEN 1 END) AS satisfied,
                        count(CASE WHEN score = 5 THEN 1 END) AS very_satisfied
                    FROM satisfaction
                    WHERE (report_id IS NOT NULL OR ndr_id IS NOT NULL)
                    AND created_at >= date_trunc('month', CURRENT_DATE - INTERVAL '1' month)
                    AND created_at <= date_trunc('month', CURRENT_DATE) - INTERVAL '1' second
            ";

            $conn = $this->em->getConnection();
            $statsStmt = $conn->prepare($satisfactionScoresQuery);
            $result = $statsStmt->executeQuery();
            $satisfactionScoresResults = $result->fetchAllAssociative();

            $satisfactionScores = [];
            $statsStartDate = (new \DateTime('FIRST DAY OF PREVIOUS MONTH'))->format('Y-m-d');

            $statsYear = (new \DateTime('FIRST DAY OF PREVIOUS MONTH'))->format('y');
            $statsMonth = (new \DateTime('FIRST DAY OF PREVIOUS MONTH'))->format('m');

            foreach ($satisfactionScoresResults[0] as $satisfactionScoreKey => $satisfactionScoreRow) {
                $satisfactionScores[] = [
                    '_timestamp' => $statsStartDate.'T00:00:00+00:00',
                    'service' => 'deputy-reporting',
                    'channel' => 'digital',
                    'count' => intval($satisfactionScoreRow),
                    'dataType' => str_replace('-', '_', $satisfactionScoreKey),
                    'period' => 'month',
                ];
            }

            $satisfactionScoresJson = json_encode($satisfactionScores, JSON_PRETTY_PRINT);

            $s3FileName = 'complete_the_deputy_report_'.$statsYear.'_'.$statsMonth.'.json';

            $this->s3SatisfactionDataStorage->store($s3FileName, $satisfactionScoresJson);

            $output->writeln('satisfaction_performance_stats - success - Successfully extracted the satisfaction scores for Digideps');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('satisfaction_performance_stats - failure - Failed to extract the satisfaction scores for Digideps');
            $output->writeln($e);

            return 1;
        }
    }
}
