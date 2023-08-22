<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StaffUserDeletionCommand extends Command
{
    public static $defaultName = 'digideps:nonexistent-sirius-staff-accounts';

    public static array $digidepsStaffToExcludeFromDeletion =
        [
            35636 => 'gugandeep.chani@digital.justice.gov.uk',
            34368 => 'james.warren@digital.justice.gov.uk',
            49768 => 'nicola.mcmillan@digital.justice.gov.uk',
            46417 => 'oludara.oginni@digital.justice.gov.uk',
            29876 => 'jack.goodby@digital.justice.gov.uk',
            45515 => 'mia.gordon@digital.justice.gov.uk',
            33833 => 'caroline.hufton@digital.justice.gov.uk',
            43009 => 'jude.rattle@digital.justice.gov.uk',
            43411 => 'tom.gulliver@digital.justice.gov.uk',
            43177 => 'peter.scottreid@digital.justice.gov.uk',
        ];

    public function __construct(
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes staff accounts that do not exist in Sirius')
            ->addArgument('id', InputArgument::IS_ARRAY, 'Pass the id of staff users to be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usersToBeDeleted = $input->getArgument('id');

        foreach ($usersToBeDeleted as $key => $id) {
            if (is_string($id)) {
                $usersToBeDeleted[$key] = intval($id);
            }

            foreach (self::$digidepsStaffToExcludeFromDeletion as $digidepsUserId => $digidepsUserEmail) {
                if ($id == $digidepsUserId) {
                    unset($usersToBeDeleted[$key]);
                }
            }
        }

        $this->userRepository->deleteStaffUsersThatAreNotInSirius($usersToBeDeleted);

        $output->writeln(sprintf(' %d users deleted', count($usersToBeDeleted)));

        return 1;
    }
}
