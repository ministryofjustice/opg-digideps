<?php

namespace App\Command;

use App\Entity\UserResearch\UserResearchResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserResearchCleanupCommand extends Command
{
    protected static $defaultName = 'digideps:delete-null-user-research-ids';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $deleteCount = $this->deleteNullIdUsers();
            $output->writeln("delete-null-user-research-ids - success - Deleted $deleteCount lay user(s) from user research who have had their user account deleted");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('delete-null-user-research-ids - failure - Failed to delete lay user(s) from user research who have had their user account deleted');
            $output->writeln($e);

            return Command::FAILURE;
        }
    }

    private function deleteNullIdUsers(): int
    {
        $deleteQb = $this->em->createQueryBuilder()
            ->delete()
            ->from(UserResearchResponse::class, 'ur')
            ->where('ur.user is null');

        $deleteCount = $deleteQb->getQuery()->execute();

        return $deleteCount;
    }
}
