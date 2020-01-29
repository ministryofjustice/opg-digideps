<?php

namespace AppBundle\Command;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    protected static $defaultName = 'digideps:cleanup';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

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
        $deleteQb = $this->em->createQueryBuilder()
            ->delete()
            ->from(User::class, 'u')
            ->where('u.id in (:ids)')
            ->setParameter('ids', $this->getInactiverUserIds());

        $deleteCount = $deleteQb->getQuery()->execute();

        return $deleteCount;
    }

    private function getInactiverUserIds(): array
    {
        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));

        $reportSubquery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(Report::class, 'r')
            ->andWhere('r.client = c');

        $ndrSubquery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(Ndr::class, 'n')
            ->andWhere('n.client = c');

        $qb = $this->em->createQueryBuilder();
        $qb->select('u.id')
            ->from(User::class, 'u')
            ->leftJoin('u.clients', 'c')
            ->andWhere('u.registrationDate < :reg_cutoff')
            ->andWhere('u.roleName = :lay_deputy_role')
            ->andWhere($qb->expr()->not($qb->expr()->exists($reportSubquery->getDQL())))
            ->andWhere($qb->expr()->not($qb->expr()->exists($ndrSubquery->getDQL())))
            ->setParameter('reg_cutoff', $thirtyDaysAgo)
            ->setParameter('lay_deputy_role', User::ROLE_LAY_DEPUTY);

        return $qb->getQuery()->getResult();
    }
}
