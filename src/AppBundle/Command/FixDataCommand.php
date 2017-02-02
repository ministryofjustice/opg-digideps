<?php

namespace AppBundle\Command;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Report\Report;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add data that wasn't added with listeners
 * Firstly wrote when data wasn't added with temporary 103 user on staging
 *
 * @codeCoverageIgnore
 */
class FixDataCommand extends AddSingleUserCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:fix-data')
            ->setDescription('add missing data to reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        /* @var $em \Doctrine\ORM\EntityManager */

        $reportRepo = $em->getRepository(Report::class);
        foreach ($reportRepo->findAll() as $entity) {
            $debtsAdded = $reportRepo->addDebtsToReportIfMissing($entity);
            $shortMoneyCatsAdded = $reportRepo->addMoneyShortCategoriesIfMissing($entity);
            $output->writeln("Report {$entity->getId()}: $debtsAdded debts, $shortMoneyCatsAdded money short cars added");
        }

        $odrRepo = $em->getRepository(Odr::class);
        foreach ($odrRepo->findAll() as $entity) {
            $debtsAdded = $odrRepo->addDebtsToOdrIfMissing($entity);
            $incomeBenefitsAdded = $odrRepo->addIncomeBenefitsToOdrIfMissing($entity);
            $output->writeln("Report {$entity->getId()}: $debtsAdded debts, $incomeBenefitsAdded income benefits added");
        }

        $em->flush();
    }
}
