<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCleanupCommand extends Command
{
    protected static $defaultName = 'digideps:delete-zero-activity-users';

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $deleteCount = $this->deleteInactivateUsers();
            $output->writeln("delete_zero_activity_users - success - Deleted $deleteCount lay user(s) that have never had any activity after 30 days of registration");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('delete_zero_activity_users - failure - Failed to delete lay user(s) that have never had any activity after 30 days of registration');
            $output->writeln($e);

            return Command::FAILURE;
        }
    }

    private function deleteInactivateUsers(): int
    {
        $inactiveUserIds = $this->userRepository->findInactive();

        $deleteQb = $this->em->createQueryBuilder()
            ->delete()
            ->from(User::class, 'u')
            ->where('u.id in (:ids)')
            ->setParameter('ids', $inactiveUserIds);

        $deleteCount = $deleteQb->getQuery()->execute();

        return $deleteCount;
    }
}
