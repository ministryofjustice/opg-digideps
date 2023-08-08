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
//            ->setName($this->defaultName)
            ->setDescription('Deletes inactive admin user accounts')
        ;
    }

      protected function execute(InputInterface $input, OutputInterface $output)
      {
          /** @var User $user */
          $getInactiveAdminUsers = $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months');

          $inactiveAdminUserIds = [];

          if ($getInactiveAdminUsers) {
              if (is_array($getInactiveAdminUsers)) {
                  foreach ($getInactiveAdminUsers as $adminUser) {
                      $inactiveAdminUserIds[] = $adminUser->getId();
                      $this->auditLogDeletionAutomation($adminUser);
                  }
              } elseif ($getInactiveAdminUsers instanceof User) {
                  $inactiveAdminUserIds[] = $getInactiveAdminUsers->getId();
                  $this->auditLogDeletionAutomation($getInactiveAdminUsers);
              }
          }

          if (!empty($inactiveAdminUserIds)) {
              $countOfAdminUsers = count($inactiveAdminUserIds);

              $this->userRepository->deleteInactiveAdminUsers($inactiveAdminUserIds);
              $output->writeln(sprintf('%d inactive admin user(s) deleted', $countOfAdminUsers));
          } else {
              $output->writeln('No inactive admin users to delete');
          }

          return 0;
      }

        private function auditLogDeletionAutomation(User $user)
        {
            $trigger = AuditEvents::USER_DELETED_AUTOMATION;
            $event = new UserRetentionPolicyCommandEvent($user, $trigger);

            $this->eventDispatcher->dispatch($event, UserRetentionPolicyCommandEvent::NAME);

            $this->logger->info(sprintf('Deleted user account with id: %d at admin permission level due to 2 year expiry.', $user->getId()));
        }
}
