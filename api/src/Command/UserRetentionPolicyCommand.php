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

    public static $defaultName = 'digideps:user-retention-policy';

    private array $inactiveAdminUserIds = [];
    
    private array $excludedUsers = [];

    public function __construct(
        private UserRepository $userRepository,
        private ObservableEventDispatcher $eventDispatcher,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Deletes inactive admin user accounts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): bool
    {
        $getInactiveAdminUsers = $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months');
        $this->excludedUsers = $this->userRepository->getAllDeletionProtectedAccounts();

        if (is_array($getInactiveAdminUsers)) {
            foreach ($getInactiveAdminUsers as $adminUser) {
                $this->storeUserIdForDeletion($adminUser);
            }
        }

        if ($getInactiveAdminUsers instanceof User) {
            $this->storeUserIdForDeletion($getInactiveAdminUsers);
        }

        if (!empty($this->inactiveAdminUserIds)) {
            $countOfAdminUsers = count($this->inactiveAdminUserIds);

            $this->userRepository->deleteInactiveAdminUsers($this->inactiveAdminUserIds);
            $output->writeln(sprintf('%d inactive admin user(s) deleted', $countOfAdminUsers));

            return true;
        }

        $output->writeln('No inactive admin users to delete');

        return false;
    }

    private function storeUserIdForDeletion(User $user): void
    {
        if (in_array($user->getId(), $this->excludedUsers)) {
            $this->inactiveAdminUserIds[] = $user->getId();

            $event = new UserRetentionPolicyCommandEvent($user, AuditEvents::USER_DELETED_AUTOMATION);
            $this->eventDispatcher->dispatch($event, UserRetentionPolicyCommandEvent::NAME);

            $this->logger->info(
                sprintf('Deleted user account with id: %d at admin permission level due to 2 year expiry.', 
                    $user->getId()
                )
            );
        }
    }
}
