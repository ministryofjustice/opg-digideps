<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    protected static $defaultName = 'digideps:cleanup';

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
        $deleteCount = $this->deleteInactivateUsers();
        $output->writeln("Deleted $deleteCount inactive user(s)");

        return 0;
    }

    private function deleteInactivateUsers(): int
    {
        $inactiveUsers = $this->userRepository->findInactive('u.id');
        $inactiveUserIds = array_column($inactiveUsers, 'id');

        $deleteQb = $this->em->createQueryBuilder()
            ->delete()
            ->from(User::class, 'u')
            ->where('u.id in (:ids)')
            ->setParameter('ids', $inactiveUserIds);

        $deleteCount = $deleteQb->getQuery()->execute();

        return $deleteCount;
    }
}
