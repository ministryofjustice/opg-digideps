<?php

namespace AppBundle\Command;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @deprecated use fixtures command instead. if removed,
 * move login into subclass FixturesCommand
 *
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
            ->addOption('roleName', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
            ->addOption('enable-odr', null, InputOption::VALUE_NONE)
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
            'odrEnabled' => $input->getOption('enable-odr'),
        ];
        if (count(array_filter($data)) !== count($data)) {
            throw new \RuntimeException('Missing params');
        }

        $this->addSingleUser($output, $data, ['flush' => true]);
    }

    /**
     * @param OutputInterface $output
     * @param string          $email
     * @param array           $data   keys: firstname lastname roleId password odrEnabled
     */
    protected function addSingleUser(OutputInterface $output, array $data, array $options)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $userRepo = $em->getRepository('AppBundle\Entity\User');
        $email = $data['email'];

        $output->write("User $email: ");

        if ($userRepo->findBy(['email' => $email])) {
            $output->writeln('skip.');

            return;
        }

        $user = (new User())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($email)
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setOdrEnabled(!empty($data['odrEnabled']))
        ;

        // role
        if (!empty($data['roleId'])) { //deprecated
            $user->setRoleName(User::roleIdToName($data['roleId']));
        } else if (!empty($data['roleName'])) {
            $user->setRoleName($data['roleName']);
        } else {
            $output->write('roleId or roleName must be defined');
            return;
        }

        $user->setPassword($this->encodePassword($user, $data['password']));

        // check params
        $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
        if ($violations->count()) {
            $output->writeln("error: $violations");

            return;
        }

        $em->persist($user);
        if ($options['flush']) {
            $em->flush($user);
        }

        $output->writeln('created.');
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
