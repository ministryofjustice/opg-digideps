<?php

namespace AppBundle\Command;

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
            ->setDescription('Add data from fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em');
        /* @var $em \Doctrine\ORM\EntityManager */
        $em->clear();

        // user and roles
        $fixtures = (array) $this->getContainer()->getParameter('fixtures');
        foreach ($fixtures as $email => $data) {
            $this->addSingleUser($output, ['email' => $email] + $data, ['flush' => false]);
        }
        $em->flush();
    }
}
