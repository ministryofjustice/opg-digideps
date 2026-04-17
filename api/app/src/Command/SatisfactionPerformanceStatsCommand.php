<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SatisfactionRepository;
use App\Service\File\Storage\S3SatisfactionDataStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SatisfactionPerformanceStatsCommand extends Command
{
    public static $defaultName = 'digideps:satisfaction-performance-stats';

    public function __construct(
        private readonly SatisfactionRepository $satisfactionRepository,
        private readonly S3SatisfactionDataStorage $s3SatisfactionDataStorage,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Fetches the satisfaction scores for Digideps and prepares the json data for update on the Opg Services Performance Data repo');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $statsStartDate = new \DateTime('FIRST DAY OF PREVIOUS MONTH');
            $statsEndDate = new \DateTime('FIRST DAY OF THIS MONTH')->sub(new \DateInterval('PT1S'));
            $satisfactionScores = $this->satisfactionRepository->getSatisfactionDataForPeriod($statsStartDate, $statsEndDate);

            if (empty($satisfactionScores)) {
                $output->writeln('satisfaction_performance_stats - possible error - No satisfaction scores found for Digideps');
            }

            $satisfactionScoresJson = json_encode($satisfactionScores, JSON_PRETTY_PRINT);

            $s3FileName = 'complete_the_deputy_report_' .
                $statsStartDate->format('y') . '_' .
                $statsStartDate->format('m') . '.json';

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
