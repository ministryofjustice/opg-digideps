<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\User;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * @codeCoverageIgnore
 */
class AddSingleUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:add-user')
            ->setDescription('Add single user from ')
            ->addArgument('email', null, InputOption::VALUE_REQUIRED)
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED)
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED)
            ->addOption('role', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [
            'firstname' => $input->getOption('firstname'),
            'lastname' => $input->getOption('lastname'),
            'roleId' => $input->getOption('role'),
            'password' => $input->getOption('password'),
            'email' => $input->getArgument('email'),
        ];
        if (count(array_filter($data)) !== count($data)) {
            throw new \RuntimeException('Missing params');
        }

        $this->addSingleUser($output, $data, ['flush' => true]);
    }

    /**
     * @param OutputInterface $output
     * @param string          $email
     * @param array           $data   keys: firstname lastname roleId password
     */
    protected function addSingleUser(OutputInterface $output, array $data, array $options)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $userRepo = $em->getRepository('AppBundle\Entity\User');
        $roleRepo = $em->getRepository('AppBundle\Entity\Role');
        $email = $data['email'];

        if ($userRepo->findBy(['email' => $email])) {
            $output->writeln("User $email already existing.");

            return;
        }

        $role = $roleRepo->find($data['roleId']);
        if (!$role) {
            $output->writel("Cannot add user $email: role {$data['roleId']} not found");
            
            return;
        }
        $user = (new User())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($email)
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setRole($role);

        $user->setPassword($this->encodePassword($user, $data['password']));

        // check params
        $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
        if ($violations->count()) {
            $output->writeln("Cannot add user $email: $violations");

            return;
        }

        $em->persist($user);
        if ($options['flush']) {
            $em->flush($user);
        }

        $output->writeln("User $email created.");
    }

    /**
     * @param User   $user
     * @param string $passwordPlain
     * 
     * @return string encoded password
     */
    protected function encodePassword(User $user, $passwordPlain)
    {
        return $this->getContainer()->get('security.encoder_factory')
            ->getEncoder($user)
            ->encodePassword($passwordPlain, $user->getSalt());
    }
}
