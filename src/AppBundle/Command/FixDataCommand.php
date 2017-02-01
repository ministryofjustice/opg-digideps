<?php

namespace AppBundle\Command;

use AppBundle\Entity\CourtOrderType;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\TransactionType;
use AppBundle\Entity\Report\TransactionTypeIn;
use AppBundle\Entity\Report\TransactionTypeOut;
use AppBundle\Entity\Role;
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

    protected function cot(OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        $cotRepo = $em->getRepository('AppBundle\Entity\CourtOrderType');
        foreach (CourtOrderType::$fixtures as $id => $name) {
            $output->write("COT $id ($name): ");
            if ($cotRepo->find($id)) {
                $output->writeln('skip');
            } else {
                $cot = new CourtOrderType();
                $cot
                    ->setId($id)
                    ->setName($name);
                $em->persist($cot);
                $output->writeln('added');
            }
        }
        $em->flush();
    }

    protected function roles(OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        $roleRepo = $em->getRepository('AppBundle\Entity\Role');
        foreach (Role::$fixtures as $id => $nr) {
            list($nameString, $roleString) = $nr;
            $output->write("Role $id ($nameString, $roleString): ");
            if ($roleRepo->find($id)) {
                $output->writeln('skip');
            } else {
                $role = new Role();
                $role
                    ->setId($id)
                    ->setRole($roleString);
                $em->persist($role);
                $output->writeln('added');
            }
        }
        $em->flush();
    }

    /**
     * @deprecated
     *
     * @param OutputInterface $output
     */
    protected function transactionTypes(OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        $tt = $em->getRepository('AppBundle\Entity\Report\TransactionType');
        foreach (TransactionType::$fixtures as $row) {
            //id, has_more_details, display_order, category, type
            list($id, $hasMoreDetails, $displayOrder, $category, $type) = $row;
            $output->write("Transaction Type $id: ");
            if ($tt->find($id)) {
                $output->writeln('skip');
            } else {
                $t = ($type == 'in') ? new TransactionTypeIn() : new TransactionTypeOut();
                $t
                    ->setId($id)
                    ->setHasMoreDetails($hasMoreDetails)
                    ->setDisplayOrder(intval($displayOrder))
                    ->setCategory($category);
                $em->persist($t);
                $output->writeln('added');
            }
        }
        $em->flush();
    }
}
