<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\TransactionType;
use AppBundle\Entity\Report\TransactionTypeIn;
use AppBundle\Entity\Report\TransactionTypeOut;
use AppBundle\Entity\Role;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
