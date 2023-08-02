<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\v2\Controller\ControllerTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserRetentionPolicyCommand extends Command
{
    use ControllerTrait;

    private $userRepository;

    protected function configure(): void
    {
        $this
            ->setName('digideps:user-retention-policy')
            ->setDescription('Deletes inactive admin user accounts')
        ;
    }

      protected function execute(InputInterface $input, OutputInterface $output): JsonResponse
      {
          /** @var User $user */
          $getInactiveAdminUsers = $this->userRepository->getAllAdminAccountsNotUsedWithin('-14 months');

          $inactiveAdminUserIds = [];

          foreach ($getInactiveAdminUsers as $adminUser) {
              foreach ($adminUser as $user) {
                  $inactiveAdminUserIds[] = $user['id'];
              }
          }

          $this->userRepository->deleteInactiveAdminUsers($inactiveAdminUserIds);

          return $this->buildSuccessResponse([], '', Response::HTTP_NO_CONTENT);
      }
}
