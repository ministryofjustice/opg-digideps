<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class DebugCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:debug')
            ->setDescription('temporary code for dev purposes')
//            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Plaintext password')
//            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'User ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailerFactory = $this->getContainer()->get('mailer.factory');
        die;
        $user = $this->getContainer()->get('em')->getRepository('AppBundle\Entity\User')->find(1);
        $mailerFactory->sendActivationEmail($user);

//        echo file_get_contents("/tmp/dd_mail_mock");
    }
}
