<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Model\SelfRegisterData;
use Doctrine\ORM\EntityManager;

class UserRegistrationService
{
    /** @var EntityManager */
    private $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $userRepository;

    /** @var \Doctrine\ORM\EntityRepository */
    private $casRecRepo;

    public function __construct($em)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('AppBundle\Entity\User');
        $this->casRecRepo = $this->em->getRepository('AppBundle\Entity\CasRec');
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

        // Casrec checks
        $casRec = $this->casRecChecks($user, $client);
        $user->setDeputyNo($casRec->getDeputyNo());

        $this->saveUserAndClient($user, $client);

        return $user;
    }

    /**
     * CASREC checks
     * - throw error 425 if case number already used
     * - throw error 421 if user and client not found
     * - throw error 424 if user and client are found but the postcode doesn't match
     * (see <root>/README.md for more info. Keep the readme file updated with this logic).
     *
     * @param User   $user
     * @param Client $client
     *
     * @return CasRec
     */
    private function casRecChecks(User $user, Client $client)
    {
        $caseNumber = CasRec::normaliseCaseNumber($client->getCaseNumber());

        $criteria = [
            'caseNumber' => $caseNumber,
            'clientLastname' => CasRec::normaliseSurname($client->getLastname()),
            'deputySurname' => CasRec::normaliseSurname($user->getLastname()),
        ];

        $clientRepo = $this->em->getRepository('AppBundle\Entity\Client');
        if ($clientRepo->findOneBy(['caseNumber' => $caseNumber])) {
            throw new \RuntimeException('User registration: Case number already used', 425);
        }

        $casRec = $this->casRecRepo->findOneBy($criteria); /** @var $casRec CasRec */
        if (!$casRec) {
            throw new \RuntimeException('User registration: not found', 421);
        }

        // if the postcode is set in CASREC, it has to match to the given one
        if ($casRec->getDeputyPostCode() &&
            $casRec->getDeputyPostCode() != CasRec::normalisePostCode($user->getAddressPostcode())) {
            $message = sprintf('User [%s] and client [%s, case number: %s] found in CasRec, but wrong postcode: [%s] expected, [%s] given',
                $user->getLastname(), $client->getLastname(), $caseNumber,
                $casRec->getDeputyPostCode(), $user->getAddressPostcode());

            throw new \RuntimeException('User registration: postcode mismatch', 424);
        }

        return $casRec;
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
            /* @var Client $client */
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
        $user->setFirstname($selfRegisterData->getFirstname());
        $user->setLastname($selfRegisterData->getLastname());
        $user->setEmail($selfRegisterData->getEmail());
        $user->setAddressPostcode($selfRegisterData->getPostcode());
        $user->setActive(false);
        $user->setRoleName(User::ROLE_LAY_DEPUTY);
        $user->setRegistrationDate(new \DateTime());
    }

    public function populateClient(Client $client, SelfRegisterData $selfRegisterData)
    {
        $client->setFirstname($selfRegisterData->getClientFirstname());
        $client->setLastname($selfRegisterData->getClientLastname());
        $client->setCaseNumber($selfRegisterData->getCaseNumber());
    }

    public function userIsUnique(User $user)
    {
        if ($user->getEmail() && $this->userRepository->findOneBy(['email' => $user->getEmail()])) {
            return false;
        } else {
            return true;
        }
    }
}
