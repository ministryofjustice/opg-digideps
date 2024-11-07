<?php

namespace App\Command;

use App\Repository\ChecklistRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResyncResubmittableErrorChecklists extends Command
{
    protected static $defaultName = 'digideps:resync-resubmittable-error-checklists';

    /** @var ChecklistRepository */
    private $checklistRepository;

    public function __construct(ChecklistRepository $checklistRepository)
    {
        $this->checklistRepository = $checklistRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $updatedChecklists = $this->checklistRepository->getResubmittableErrorChecklistsAndSetToQueued('100');
            $output->writeln('resync_resubmittable_error_checklists - success - Updated '.count($updatedChecklists).' checklists back to QUEUED status');

            return 0;
        } catch (Exception $e) {
            $output->writeln('resync_resubmittable_error_checklists - failure - Failed to update checklists back to QUEUED status');
            $output->writeln($e);

            return 1;
        }
    }
}
