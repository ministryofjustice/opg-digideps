<?php

namespace AppBundle\Command;

use AppBundle\Service\StatsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class StatsCreateCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:stats-create')
            ->addArgument('file')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, '', 300)
            ->setDescription('Get CSV of stats ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // avoid being executed concurrently on multiple API boxes and stress the db too much
        if ($sleep = rand(0, $input->getOption('sleep', 0))) {
            $output->write("Sleeping $sleep seconds to avoid multiple execution from APIs");
        }

        $statsService = $this->getContainer()->get('stats_service'); /* @var $statsService StatsService */

        $file = $input->getArgument('file');
        if (!$file) {
            throw new \RuntimeException('specify a file name');
        }

        $output->write("Writing stats into $file ...");
        $ret = $statsService->saveCsv($file);

        $output->writeln("$ret lines written into $file");
    }
}
