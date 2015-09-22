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
        ;
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityRepository */
        $userRepo = $em->getRepository('AppBundle\Entity\User');
        $roleRepo = $em->getRepository('AppBundle\Entity\Role');
        
        $fixtures = $this->getContainer()->getParameter('fixtures');
        if (empty($fixtures)) {
            $output->writeln("No data fixture to add.");
        }
        foreach ($fixtures as $email => $data) {
            if ($userRepo->findBy(['email'=>$email])) {
                $output->writeln("User $email already existing.");
                continue;
            }
            
            $em->clear();
            $user = (new User)
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setEmail($email)
                    ->setActive(true)
                    ->setRole($roleRepo->find($data['roleId']));
            
            $user->setPassword($this->encodePassword($user, $data['password']));

            // check params
            $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
            if ($violations->count()) {
                $output->writeln("Cannot add user $email: $violations");
                continue;
            }
            
            $em->persist($user);
            $em->flush($user);
            
            $output->writeln("User $email created.");
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