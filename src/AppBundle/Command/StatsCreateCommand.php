<?php

namespace AppBundle\Command;

use AppBundle\Service\StatsService;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('Get CSV of stats ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // avoid being executed concurrently on multiple API boxes and stress the db too much
        sleep(rand(1, 300));
        $statsService = $this->getContainer()->get('stats_service'); /* @var $statsService StatsService */

        $file = $input->getArgument('file');
        if (!$file) {
            throw new \RuntimeException('specify a file name');
        }

        $data = $statsService->getRecordsCsv();
        $ret = file_put_contents($file, $data);

        if (!$ret) {
            throw new \RuntimeException("cannot write into $file");
        }

        $output->writeln("$ret bytes written into $file");
    }
}
