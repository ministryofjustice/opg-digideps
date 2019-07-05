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

    /**
     * @var CasrecVerificationService
     */
    private $casrecVerificationService;

    public function __construct(EntityManager $em, CasrecVerificationService $casrecVerificationService)
    {
        $this->em = $em;
        $this->casrecVerificationService = $casrecVerificationService;
    }

    /**
     * CASREC checks
     * - throw error 403 if user is a co-deputy attempting to self-register
     * - throw error 421 if user and client not found
     * - throw error 422 if user email is already found
     * - throw error 424 if user and client are found but the postcode doesn't match
     * - throw error 425 if client is already used
     * (see <root>/README.md for more info. Keep the readme file updated with this logic).
     *
     * @param SelfRegisterData $selfRegisterData
     *
     * @throws \RuntimeException
     *
     * @return User
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData)
    {
        $isMultiDeputyCase = $this->casrecVerificationService->isMultiDeputyCase($selfRegisterData->getCaseNumber());
        $existingClient = $this->em->getRepository('AppBundle\Entity\Client')->findOneByCaseNumber(CasRec::normaliseCaseNumber($selfRegisterData->getCaseNumber()));

        // ward off non-fee-paying codeps trying to self-register
        if ($isMultiDeputyCase && $existingClient instanceof Client && $existingClient->hasDeputies()) {
            // if client exists with case number, the first codep already registered.
            throw new \RuntimeException('Co-deputy cannot self register.', 403);
        }

        // Check the user doesn't already exist
        $existingUser = $this->em->getRepository('AppBundle\Entity\User')->findOneByEmail($selfRegisterData->getEmail());
        if ($existingUser) {
            throw new \RuntimeException("User with email {$existingUser->getEmail()} already exists.", 422);
        }
        $users = $existingClient->getUsers();

        // Check the client is unique and has no deputies attached
        if ($existingClient instanceof Client && $existingClient->hasDeputies()) {
            throw new \RuntimeException('User registration: Case number already used', 425);
        }

        $user = new User();
        $user->recreateRegistrationToken();
        $this->populateUser($user, $selfRegisterData);

        $client = new Client();
        if (!$existingClient->hasDeputies()) {
            // use existing client if this registration is a new deputy against an undeputised Client
            $client = $existingClient;
        }

        $this->populateClient($client, $selfRegisterData);

        $this->casrecVerificationService->validate($selfRegisterData->getCaseNumber(), $selfRegisterData->getClientLastname(), $selfRegisterData->getLastname(), $user->getAddressPostcode()
                                      );

        $user->setDeputyNo(implode(',', $this->casrecVerificationService->getLastMatchedDeputyNumbers()));
        $user->setCoDeputyClientConfirmed($isMultiDeputyCase);
        $user->setNdrEnabled($this->casrecVerificationService->isLastMachedDeputyNdrEnabled());

        $this->saveUserAndClient($user, $client);
        return $user;
    }

    /**
     * @param SelfRegisterData $selfRegisterData
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function validateCoDeputy(SelfRegisterData $selfRegisterData)
    {
        $user = $this->em->getRepository('AppBundle\Entity\User')->findOneByEmail($selfRegisterData->getEmail());
        if (!($user)) {
            throw new \RuntimeException('User registration: not found', 421);
        }

        if ($user->getCoDeputyClientConfirmed()) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        $this->casrecVerificationService->validate($selfRegisterData->getCaseNumber(), $selfRegisterData->getClientLastname(), $selfRegisterData->getLastname(), $selfRegisterData->getPostcode()
                                                  );

        return true;
    }

    /**
     * @param User   $user
     * @param Client $client
     *
     * @throws \Exception
     */
    public function saveUserAndClient(User $user, Client $client)
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
        } catch (\Throwable $e) {
            // Rollback the failed transaction attempt
            $connection->rollback();
            throw $e;
        }
    }

    /**
     * @param User             $user
     * @param SelfRegisterData $selfRegisterData
     */
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

    /**
     * @param Client           $client
     * @param SelfRegisterData $selfRegisterData
     */
    public function populateClient(Client $client, SelfRegisterData $selfRegisterData)
    {
        $client->setFirstname($selfRegisterData->getClientFirstname());
        $client->setLastname($selfRegisterData->getClientLastname());
        $client->setCaseNumber($selfRegisterData->getCaseNumber());
    }
}
