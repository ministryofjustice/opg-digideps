<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:passwordEncode')
            ->setDescription('Generate password hash given plain text password')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Plaintext password')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'User ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $passwordPlain = $input->getOption('password');
        $userId = $input->getOption('user');
        
        $user = $this->getContainer()->get('apiclient')->getEntity('User', 'user/' . $userId);
        $encoder = $this->getContainer()->get('security.encoder_factory')->getEncoder($user);
        $out = $encoder->encodePassword($passwordPlain, $user->getSalt());
        
        $output->writeln($out);
    }
}