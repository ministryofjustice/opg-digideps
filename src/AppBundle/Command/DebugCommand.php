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
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Plaintext password'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('password');
        
        $user = $this->getContainer()->get('apiclient')->getEntity('User', 'user/1');
        
        $encoder = $this->getContainer()->get('security.encoder_factory')->getEncoder($user);
        $out = $encoder->encodePassword($name, '');
        
        $output->writeln($out);
    }
}