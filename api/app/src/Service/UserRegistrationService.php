<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use App\Model\SelfRegisterData;
use Doctrine\ORM\EntityManagerInterface;

class UserRegistrationService
{
    private string $selfRegisterCaseNumber = '';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PreRegistrationVerificationService $preRegistrationVerificationService,
    ) {
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
     * @return User
     *
     * @throws \RuntimeException
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData)
    {
        $caseNumber = $selfRegisterData->getCaseNumber() ?? '';

        $isMultiDeputyCase = $this->preRegistrationVerificationService->isMultiDeputyCase($caseNumber);
        $existingClient = $this->em->getRepository(Client::class)->findByCaseNumber($caseNumber);

        // ward off non-fee-paying codeps trying to self-register
        if ($isMultiDeputyCase && ($existingClient instanceof Client) && $existingClient->hasDeputies()) {
            // if client exists with case number, the first codep already registered.
            throw new \RuntimeException(json_encode('Co-deputy cannot self register.'), 403);
        }

        // Check the user doesn't already exist
        $existingUser = $this->em->getRepository(User::class)->findOneByEmail($selfRegisterData->getEmail());
        if ($existingUser) {
            throw new \RuntimeException(json_encode(sprintf('User with email %s already exists.', $existingUser->getEmail())), 422);
        }

        // Check the client is unique and has no deputies attached
        if ($existingClient instanceof Client) {
            if ($existingClient->hasDeputies() || $existingClient->getOrganisation() instanceof Organisation) {
                throw new \RuntimeException(json_encode(sprintf('User registration: Case number %s already used', $existingClient->getCaseNumber())), 425);
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

        $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $user->getAddressPostcode()
        );

        if (1 == count($this->preRegistrationVerificationService->getLastMatchedDeputyNumbers())) {
            $user->setDeputyNo($this->preRegistrationVerificationService->getLastMatchedDeputyNumbers()[0]);
            $user->setDeputyUid($this->preRegistrationVerificationService->getLastMatchedDeputyNumbers()[0]);
            $user->setPreRegisterValidatedDate(new \DateTime('now'));
            $user->setRegistrationRoute(User::SELF_REGISTER);

            if ($this->preRegistrationVerificationService->isSingleDeputyAccount()) {
                $user->setIsPrimary(true);
            }
        } else {
            // A deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            throw new \RuntimeException(json_encode(sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber())), 462);
        }

        $user->setNdrEnabled($this->preRegistrationVerificationService->isLastMachedDeputyNdrEnabled());

        $this->saveUserAndClient($user, $client);

        return $user;
    }

    /**
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function validateCoDeputy(SelfRegisterData $selfRegisterData)
    {
        $user = $this->em->getRepository(User::class)->findOneByEmail($selfRegisterData->getEmail());
        if (!$user) {
            throw new \RuntimeException('User registration: not found', 421);
        }

        if ($user->getCoDeputyClientConfirmed()) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.", 422);
        }

        $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $selfRegisterData->getPostcode()
        );

        // store case number in class property to access in retrieveCoDeputyUid exception
        $this->selfRegisterCaseNumber = $selfRegisterData->getCaseNumber();

        return true;
    }

    /**
     * @return string
     *
     * @throws \RuntimeException
     */
    public function retrieveCoDeputyUid()
    {
        if (1 == count($this->preRegistrationVerificationService->getLastMatchedDeputyNumbers())) {
            return $this->preRegistrationVerificationService->getLastMatchedDeputyNumbers()[0];
        } else {
            // A deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            throw new \RuntimeException(json_encode(sprintf('A unique deputy record for case number %s could not be identified', $this->selfRegisterCaseNumber)), 462);
        }
    }

    /**
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
