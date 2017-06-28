<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixPaReportingStartDateCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-reporting-start-date')
            ->setDescription('Fixes report start date for PA clients reports. Instead of just 1 year, its now -1 year + 1 day ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');

        $output->write('Fixing data. Please wait ...
');
        $fixDataService = new FixDataService($em);
        $messages = $fixDataService->fixPaStartDate()->getMessages();

        foreach ($messages as $m) {
            $output->writeln($m);
        }
        $output->writeln($fixDataService->getTotalProcessed() . ' reports skipped');
        $output->writeln('Done');
    }
}
