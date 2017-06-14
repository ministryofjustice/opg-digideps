<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixSubmittedByCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-report-submitted-by')
            ->setDescription('add report.submittedBy to reports taking the first user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');

        $output->write('Adding user.submittedBy. Please wait ...');
        $fixDataService = new FixDataService($em);
        $messages = $fixDataService->fixReportSubmittedBy()->getMessages();
        foreach ($messages as $m) {
            $output->writeln($m);
        }
        $output->writeln('Done');
    }
}
