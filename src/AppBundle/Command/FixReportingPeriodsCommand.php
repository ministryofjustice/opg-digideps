<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixReportingPeriodsCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-reporting-periods')
            ->setDescription('Fixes report period for PA clients reports. Moving forward by 56 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');

        $output->write('Fixing data. Please wait ...');
        $fixDataService = new FixDataService($em);
        $messages = $fixDataService->fixPaReportingPeriods()->getMessages();

        foreach ($messages as $m) {
            $output->writeln($m);
        }
        $output->writeln($fixDataService->getTotalProcessed() . ' reports skipped');
        $output->writeln('Done');
    }
}
