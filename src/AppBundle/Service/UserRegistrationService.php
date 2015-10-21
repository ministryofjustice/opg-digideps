<?php
namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Entity\User;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Doctrine\ORM\EntityManager;
use AppBundle\Exception;

class UserRegistrationService
{

    /** @var EntityManager em */
    private $em;

    /** @var  MailFactory mailFactory */
    private $mailFactory;

    /** @var  MailSender mailServer */
    private $mailSender;

    /** @var \Doctrine\ORM\EntityRepository $userRepository */
    private $userRepository;

    /** @var \Doctrine\ORM\EntityRepository $roleRepository */
    private $roleRepository;


    public function __construct($em, $mailFactory, $mailSender)
    {
        $this->em = $em;
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
        $this->userRepository = $this->em->getRepository('AppBundle\Entity\User');
        $this->roleRepository = $this->em->getRepository('AppBundle\Entity\Role');
    }

    public function selfRegisterUser(SelfRegisterData $selfRegisterData)
    {

        $user = new User();
        $user->recreateRegistrationToken();
        $client = new Client();

        $this->populateUser($user, $selfRegisterData);
        $this->populateClient($client, $selfRegisterData);

        // Check the user is unique
        if ($this->userIsUnique($user) == false) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        $this->saveUserAndClient($user, $client);

        $mail = $this->mailFactory->createActivationEmail($user);
        $this->mailSender->send($mail);

        return $user;

    }

    public function saveUserAndClient($user, $client)
    {
        $connection = $this->em->getConnection();

        $connection->beginTransaction();

        try {

            // Save the user
            $this->em->persist($user);
            $this->em->flush();

            // Add the user to the client an save it
            /** @var Client $client */
            $client->addUser($user);
            $this->em->persist($client);
            $this->em->flush();

            // Try and commit the transaction
            $connection->commit();

        } catch (\Exception $e) {
            // Rollback the failed transaction attempt
            $connection->rollback();
            throw $e;
        }
    }

    public function populateUser(User $user, SelfRegisterData $selfRegisterData)
    {
        $role = $this->roleRepository->findOneBy(['role'=>'ROLE_LAY_DEPUTY']);

        $user->setFirstname($selfRegisterData->getFirstname());
        $user->setLastname($selfRegisterData->getLastname());
        $user->setEmail($selfRegisterData->getEmail());
        $user->setAddressPostcode($selfRegisterData->getPostcode());
        $user->setActive(false);
        $user->setEmailConfirmed(false);
        $user->setRole($role);
    }

    public function populateClient(Client $client, SelfRegisterData $selfRegisterData)
    {
        $client->setLastname($selfRegisterData->getClientLastname());
        $client->setCaseNumber($selfRegisterData->getCaseNumber());
    }

    public function userIsUnique(User $user)
    {
        if ($user->getEmail() && $this->userRepository->findOneBy(['email'=>$user->getEmail()])) {
            return false;
        } else {
            return true;
        }
    }
}