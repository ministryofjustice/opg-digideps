<?php

namespace AppBundle\Command;

use AppBundle\Entity\CourtOrderType;
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

        // court order type
        $this->cot($output);

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
}
