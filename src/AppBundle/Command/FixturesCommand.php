<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\CourtOrderType;
use AppBundle\Entity\Role;
use AppBundle\Entity\TransactionType;
use AppBundle\Entity\TransactionTypeIn;
use AppBundle\Entity\TransactionTypeOut;

/**
 * @codeCoverageIgnore
 */
class FixturesCommand extends AddSingleUserCommand
{

    protected function configure()
    {
        $this
                ->setName('digideps:fixtures')
                ->setDescription('Add data from fixtures')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $em->clear();

        // court order type
        $this->cot($output);
        
        // transaction types
        $this->transactionTypes($output);

        // user and roles
        $this->roles($output);
        $fixtures = (array) $this->getContainer()->getParameter('fixtures');
        foreach ($fixtures as $email => $data) {
            $this->addSingleUser($output, ['email' => $email] + $data, ['flush' => false]);
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
                $output->writeln("skip");
            } else {
                $cot = new CourtOrderType();
                $cot
                        ->setId($id)
                        ->setName($name);
                $em->persist($cot);
                $output->writeln("added");
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
                $output->writeln("skip");
            } else {
                $role = new Role();
                $role
                        ->setId($id)
                        ->setRole($roleString)
                        ->setName($nameString);
                $em->persist($role);
                $output->writeln("added");
            }
        }
        $em->flush();
    }

    protected function transactionTypes(OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        $tt = $em->getRepository('AppBundle\Entity\TransactionType');
        foreach (TransactionType::$fixtures as $row) {
            //id, has_more_details, display_order, category, type
            list($id, $hasMoreDetails, $displayOrder, $category, $type) = $row;
            $output->write("Transaction Type $id: ");
            if ($tt->find($id)) {
                $output->writeln("skip");
            } else {
                $t = ($type == 'in') ? new TransactionTypeIn() : new TransactionTypeOut();
                $t
                    ->setId($id)
                    ->setHasMoreDetails($hasMoreDetails)
                    ->setDisplayOrder(intval($displayOrder))
                    ->setCategory($category);
                $em->persist($t);
                $output->writeln("added");
            }
        }
        $em->flush();
    }

}
