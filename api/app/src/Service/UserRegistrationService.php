<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Model\SelfRegisterData;
use Doctrine\ORM\EntityManagerInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PreRegistrationVerificationService $preRegistrationVerificationService,
    ) {
    }

    /**
     * - throw error 403 if user is a co-deputy attempting to self-register
     * - throw error 421 if user and client not found
     * - throw error 422 if user email is already found
     * - throw error 424 if user and client are found but the postcode doesn't match
     * - throw error 425 if client is already used
     * - throw error 462 if deputy could not be uniquely identified.
     *
     * @throws \RuntimeException with one of the error codes above if self-registration failed
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData): User
    {
        $caseNumber = $selfRegisterData->getCaseNumber() ?? '';

        $isMultiDeputyCase = $this->preRegistrationVerificationService->isMultiDeputyCase($caseNumber);
        $existingClient = $this->em->getRepository(Client::class)->findByCaseNumber($caseNumber);

        // ward off non-fee-paying codeps trying to self-register
        if ($isMultiDeputyCase && ($existingClient instanceof Client) && $existingClient->hasDeputies()) {
            // if client exists with case number, the first codep already registered.
            throw new \RuntimeException(json_encode('Co-deputy cannot self register.') ?: '', 403);
        }

        // Check the user doesn't already exist
        $existingUser = $this->em->getRepository(User::class)->findOneByEmail($selfRegisterData->getEmail());
        if ($existingUser) {
            $message = sprintf('User with email %s already exists.', $existingUser->getEmail());
            throw new \RuntimeException(json_encode($message) ?: '', 422);
        }

        // Check the client is unique and has no deputies attached
        if ($existingClient instanceof Client) {
            if ($existingClient->hasDeputies() || $existingClient->getOrganisation() instanceof Organisation) {
                $message = sprintf('User registration: Case number %s already used', $existingClient->getCaseNumber());
                throw new \RuntimeException(json_encode($message) ?: '', 425);
            } else {
                // soft delete client
                $this->em->remove($existingClient);
                $this->em->flush();
            }
        }

        // proceed with brand new deputy and client
        $user = new User();
        $user->recreateRegistrationToken();
        $this->populateUser($user, $selfRegisterData);

        $client = new Client();
        $this->populateClient($client, $selfRegisterData);

        // if validation fails, this throws a runtime exception which propagates to callers of this method
        $preregMatches = $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $user->getAddressPostcode()
        );

        if (1 !== count($preregMatches)) {
            // a deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            $message = sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber());
            throw new \RuntimeException(json_encode($message) ?: '', 462);
        }

        $user->setDeputyNo($preregMatches[0]->getDeputyUid());
        $user->setDeputyUid(intval($preregMatches[0]->getDeputyUid()));
        $user->setPreRegisterValidatedDate(new \DateTime('now'));
        $user->setRegistrationRoute(User::SELF_REGISTER);

        if (!$this->preRegistrationVerificationService->deputyUidHasOtherUserAccounts($preregMatches[0]->getDeputyUid())) {
            $user->setIsPrimary(true);
        }

        $user->setNdrEnabled(true === $preregMatches[0]->getNdr());

        $this->saveUserAndClient($user, $client);

        return $user;
    }

    /**
     * @return PreRegistration[]
     *
     * @throws \RuntimeException
     */
    public function validateCoDeputy(SelfRegisterData $selfRegisterData): array
    {
        $user = $this->em->getRepository(User::class)->findOneByEmail($selfRegisterData->getEmail());
        if (!$user) {
            throw new \RuntimeException('User registration: not found', 421);
        }

        if ($user->getCoDeputyClientConfirmed()) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        // throws a variety of runtime exceptions if self registration data is invalid
        $preregMatches = $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $selfRegisterData->getPostcode()
        );

        // no exceptions thrown, so return the matched deputies
        return $preregMatches;
    }

    /**
     * @throws \Exception
     */
    private function saveUserAndClient(User $user, Client $client)
    {
        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        try {
            // Save the user
            $this->em->persist($user);
            $this->em->flush();

            // Add the user to the client then save
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

    private function populateUser(User $user, SelfRegisterData $selfRegisterData)
    {
        $user->setFirstname($selfRegisterData->getFirstname() ?? '');
        $user->setLastname($selfRegisterData->getLastname() ?? '');
        $user->setEmail($selfRegisterData->getEmail() ?? '');
        $user->setAddressPostcode($selfRegisterData->getPostcode() ?? '');
        $user->setActive(false);
        $user->setRoleName(User::ROLE_LAY_DEPUTY);
    }

    private function populateClient(Client $client, SelfRegisterData $selfRegisterData)
    {
        $client->setFirstname($selfRegisterData->getClientFirstname() ?? '');
        $client->setLastname($selfRegisterData->getClientLastname() ?? '');
        $client->setCaseNumber($selfRegisterData->getCaseNumber() ?? '');
    }
}
