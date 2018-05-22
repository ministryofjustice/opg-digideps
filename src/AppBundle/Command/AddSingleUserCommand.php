<?php

namespace AppBundle\Command;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
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
            ->addOption('enable-ndr', null, InputOption::VALUE_NONE)
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
            'ndrEnabled' => $input->getOption('enable-ndr'),
        ];
        if (count(array_filter($data)) !== count($data)) {
            throw new \RuntimeException('Missing params');
        }

        $this->addSingleUser($output, $data, ['flush' => true]);
    }

    /**
     * @param OutputInterface $output
     * @param string          $email
     * @param array           $data   keys: firstname lastname roleId password ndrEnabled
     */
    protected function addSingleUser(OutputInterface $output, array $data, array $options)
    {
        $em = $this->getContainer()->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $userRepo = $em->getRepository('AppBundle\Entity\User');
        $email = $data['email'];

        $output->write("User $email: ");

        /**
         * create User entity
         */
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
            ->setNdrEnabled(!empty($data['ndrEnabled']))
            ->setCoDeputyClientConfirmed(
                isset($data['codeputyClientConfirmed']) ?
                    (bool) $data['codeputyClientConfirmed'] :
                    false
            )
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressCountry('GB')
        ;

        if (!empty($data['deputyPostcode'])) {
            $user->setAddressPostcode($data['deputyPostcode']);
        }

        if (!empty($data['roleName'])) {
            $user->setRoleName($data['roleName']);
        } else {
            $output->write('roleName must be defined for the user');
            return;
        }

        $user->setPassword($this->encodePassword($user, $data['password']));

        $violations = $this->getContainer()->get('validator')->validate($user, 'admin_add_user'); /* @var $violations ConstraintViolationList */
        if ($violations->count()) {
            $output->writeln("error: $violations");

            return;
        }
        $em->persist($user);

        /**
         * Deputy user::
         * Add CASREC entry + Client
         */
        if (!in_array($data['roleName'], [User::ROLE_ADMIN, User::ROLE_AD, User::ROLE_CASE_MANAGER])) {
            $casRecEntity = $casRecEntity = new CasRec($this->extractDataToRow($data));
            $em->persist($casRecEntity);

            // add client
            $client = new Client();
            $client
                ->setCaseNumber($data['caseNumber'])
                ->setFirstname('John')
                ->setLastname($data['clientSurname'])
                ->setPhone('022222222222222')
                ->setAddress('Victoria road')
                ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));

            $em->persist($client);
            $user->addClient($client);

            if (!$client->getNdr()) {
                $ndr = new Ndr($client);
                $em->persist($ndr);
            }
        }


        if ($options['flush']) {
            $em->flush();
        }

        $output->writeln('created.');
    }

    /**
     * Method to convert user fixture data into Casrec CSV data required by constructor
     *
     * @param $data
     *
     * @return mixed
     */
    private function extractDataToRow($data)
    {
        $row['Case'] = $data['caseNumber'];
        $row['Surname'] = $data['clientSurname'];
        $row['Deputy No'] = $data['deputyNo'];
        $row['Dep Surname'] = $data['lastname'];
        $row['Dep Postcode'] = $data['deputyPostcode'];
        $row['Typeofrep'] = $data['typeOfReport'];
        $row['Corref'] = $data['corref'];

        return $row;
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
