<?php

namespace OPG\Digideps\Backend\Command;

use OPG\Digideps\Backend\Cleanup\ReportCleaner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCleanupCommand extends Command
{
    public function __construct(
        private readonly ReportCleaner $reportCleaner
    ) {
        parent::__construct('digideps:cleanup:reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportCleaner->clean($input->hasArgument('allow-not-continuous'));
        if ($input->hasArgument('execute-actions')) {
            $this->reportCleaner->executeActions();
        }
        return 0;
    }
}
