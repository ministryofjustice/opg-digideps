<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Service\ApiClient;
use AppBundle\Entity\User;
use Symfony\Component\Validator\ConstraintViolationList;

class AddUserCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:user-add')
            ->setDescription('User add')
            ->addArgument('email', null, InputOption::VALUE_REQUIRED, 'email')
            ->addArgument('firstname', null, InputOption::VALUE_OPTIONAL, 'firstname')
            ->addArgument('lastname', null, InputOption::VALUE_OPTIONAL, 'lastname')
            ->addArgument('password', null, InputOption::VALUE_OPTIONAL, 'password. default=test')
            ->addArgument('roleId', null, InputOption::VALUE_OPTIONAL, '1=admin, 2=deputy (default)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('email')) {
            throw new \InvalidArgumentException("Email needed");
        }
        $apiClient = $this->getContainer()->get('apiclient'); /* @var $apiClient ApiClient */
        
        $user = (new User)
             ->setFirstname($input->getArgument('firstname') ?: 'Firstname')
             ->setLastname($input->getArgument('lastname') ?: 'LastName')
             ->setEmail($input->getArgument('email'))
             ->setRoleId($input->getArgument('roleId') ?: 2);
        
        // check params
        $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
        if ($violations->count()) {
            echo $violations;die;
        }
        
        // add user
        $response = $apiClient->postC('add_user', $user, [
            'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
        ]);
        // refresh from aPI
        $user = $apiClient->getEntity('User', 'user/' . $response['id']);
        // set password and activate
        $response = $apiClient->putC('user/' . $user->getId(), json_encode([
            'password' => $this->encodePassword($user, $input->getArgument('password') ?: 'test'),
            'active' => true
        ]));
        
        $output->writeln("User  created {$user->getEmail()} password: {$input->getArgument('password')}");
    }
    
    /**
     * @param User $user
     * @param string $passwordPlain
     * 
     * @return string encoded password
     */
    private function encodePassword(User $user, $passwordPlain)
    {
        $encoder = $this->getContainer()->get('security.encoder_factory')->getEncoder($user);
        return $encoder->encodePassword($passwordPlain, $user->getSalt());
    }
}