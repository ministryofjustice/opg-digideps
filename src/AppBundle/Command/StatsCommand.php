<?php

namespace AppBundle\Command;

use AppBundle\Service\StatsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class StatsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:stats.csv')
            ->setDescription('Get CSV of stats ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statsService = $this->getContainer()->get('statsService'); /* @var $statsService StatsService */

        $csv = $statsService->getRecordsCsv();

        $output->writeln($csv);
    }
}
