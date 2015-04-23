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
                ->setName('digideps:fixtures')
                ->setDescription('Add data from fixtures')
                ->addArgument('email', null, InputOption::VALUE_REQUIRED, 'email')
                ->addArgument('firstname', null, InputOption::VALUE_OPTIONAL, 'firstname')
                ->addArgument('lastname', null, InputOption::VALUE_OPTIONAL, 'lastname')
                ->addArgument('password', null, InputOption::VALUE_OPTIONAL, 'password. default=test')
                ->addArgument('roleId', null, InputOption::VALUE_OPTIONAL, '1=admin, 2=deputy (default)')
        ;
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apiClient = $this->getContainer()->get('apiclient'); /* @var $apiClient ApiClient */

        $fixtures = $this->getContainer()->getParameter('fixtures');
        if (empty($fixtures)) {
            $output->writeln("No data fixture to add.");
        }
        foreach ($fixtures as $email => $data) {
            if ($this->userExists($email)) {
                $output->writeln("User $email already existing.");
                continue;
            }

            $user = (new User)
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setEmail($email)
                    ->setRoleId($data['roleId']);

            // check params
            $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
            if ($violations->count()) {
                $output->writeln("Cannot add user $email: $violations");
                continue;
            }

            // add user
            $response = $apiClient->postC('add_user', $user, [
                'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
            ]);
            if ($data['activated']) {
                // refresh from aPI
                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
                // set password and activate
                $response = $apiClient->putC('user/' . $user->getId(), json_encode([
                    'password' => $this->encodePassword($user, $data['password']),
                    'active' => true
                ]));
            }

            $output->writeln("User $email created.");
        }
    }

    private function userExists($email)
    {
        $apiClient = $this->getContainer()->get('apiclient'); /* @var $apiClient ApiClient */
        try {
            $apiClient->getEntity('User', 'find_user_by_email', [ 'parameters' => [ 'email' => $email]]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
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