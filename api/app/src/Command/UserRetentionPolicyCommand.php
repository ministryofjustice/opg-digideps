<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Event\UserRetentionPolicyCommandEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Repository\UserRepository;
use App\Service\Audit\AuditEvents;
use App\v2\Controller\ControllerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRetentionPolicyCommand extends Command
{
    use ControllerTrait;

    public static $defaultName = 'digideps:delete-inactive-users';

    private array $inactiveAdminUserIds = [];

    private array $excludedUsers = [];

    public function __construct(
        private UserRepository $userRepository,
        private ObservableEventDispatcher $eventDispatcher,
        private LoggerInterface $verboseLogger
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes inactive admin user accounts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $getInactiveAdminUsers = $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months');
            $this->excludedUsers = $this->userRepository->getAllDeletionProtectedAccounts();

            if (is_array($getInactiveAdminUsers)) {
                foreach ($getInactiveAdminUsers as $adminUser) {
                    $this->storeUserIdForDeletion($adminUser);
                    usleep(250000); // Sleep for 0.25 seconds (250,000 microseconds)
                }
            }

            if ($getInactiveAdminUsers instanceof User) {
                $this->storeUserIdForDeletion($getInactiveAdminUsers);
            }

            if (!empty($this->inactiveAdminUserIds)) {
                $countOfAdminUsers = count($this->inactiveAdminUserIds);

                $this->userRepository->deleteInactiveAdminUsers($this->inactiveAdminUserIds);
                $output->writeln(sprintf('delete_inactive_users - success - %d inactive admin user(s) deleted', $countOfAdminUsers));

                return Command::SUCCESS;
            }

            $output->writeln('delete_inactive_users - success - No inactive admin users to delete');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('delete_inactive_users - failure - Failed to delete inactive users');
            $output->writeln($e);

            return Command::FAILURE;
        }
    }

    private function storeUserIdForDeletion(User $user): void
    {
        if (!in_array($user->getId(), $this->excludedUsers)) {
            $this->inactiveAdminUserIds[] = $user->getId();

            $event = new UserRetentionPolicyCommandEvent($user, AuditEvents::USER_DELETED_AUTOMATION);
            $this->eventDispatcher->dispatch($event, UserRetentionPolicyCommandEvent::NAME);

            $this->verboseLogger->notice(
                sprintf('Deleted user account with id: %d at admin permission level due to 2 year expiry.',
                    $user->getId()
                )
            );
        }
    }
}
