<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Model\SelfRegisterData;
use App\Repository\ClientRepository;
use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PreRegistrationVerificationService $preRegistrationVerificationService,
    ) {
    }

    /**
     * - throw error 421 if user and client not found
     * - throw error 422 if user email is already found
     * - throw error 423 is user deputyUid is associated with another account *
     * - throw error 424 if user and client are found but the postcode doesn't match
     * - throw error 460 if a user that is associated with an organisation attempts to sign up *
     * - throw error 462 if deputy could not be uniquely identified.
     *
     * @throws \RuntimeException with one of the error codes above if self-registration failed
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->em->getRepository(Client::class);
        /** @var OrganisationRepository $orgRepository  */
        $organisationRepository = $this->em->getRepository(Organisation::class);

        $caseNumber = $selfRegisterData->getCaseNumber() ?? '';
        $email = $selfRegisterData->getEmail() ?? '';

        // Check the user doesn't already exist
        $existingUser = $userRepository->findOneByEmail($email);
        if ($existingUser) {
            $message = sprintf('User with email %s already exists.', $existingUser->getEmail());
            throw new \RuntimeException(json_encode($message) ?: '', 422);
        }

        $orgExists = $organisationRepository->findByEmailIdentifier($email);
        if ($orgExists) {
            $message = sprintf('An Organisation User attempting to sign up via Lay self-service registration pathway');
            throw new \RuntimeException(json_encode($message) ?: '', 460);
        }

        // if validation fails, this throws a runtime exception which propagates to callers of this method
        $preregMatches = $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $selfRegisterData->getPostcode()
        );

        if (1 !== count($preregMatches)) {
            // a deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            $message = sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber());
            throw new \RuntimeException(json_encode($message) ?: '', 462);
        }

        $deputyUid = $preregMatches[0]->getDeputyUid();
        if ($this->preRegistrationVerificationService->deputyUidHasOtherUserAccounts($deputyUid)) {
            $message = sprintf('A deputy with the UID %s has already been registered', $deputyUid);
            throw new \RunTimeException(json_encode($message) ?: '', 423);
        }

        $user = new User();
        $user->recreateRegistrationToken();
        $user->setPreRegisterValidatedDate(new \DateTime('now'));
        $this->populateLayUser($user, $selfRegisterData, intval($deputyUid), User::SELF_REGISTER);

        $existingClient = $clientRepository->findByCaseNumber($caseNumber);
        if ($existingClient instanceof Client) {
            $client = $existingClient;
        } else {
            $client = new Client();
            $this->populateClient($client, $selfRegisterData);
        }

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
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneByEmail($selfRegisterData->getEmail());
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

    private function populateLayUser(User $user, SelfRegisterData $selfRegisterData, int $deputyUid, string $registrationType): void
    {
        $user->setFirstname($selfRegisterData->getFirstname() ?? '');
        $user->setLastname($selfRegisterData->getLastname() ?? '');
        $user->setEmail($selfRegisterData->getEmail() ?? '');
        $user->setAddressPostcode($selfRegisterData->getPostcode() ?? '');
        $user->setActive(false);
        $user->setRoleName(User::ROLE_LAY_DEPUTY);
        $user->setIsPrimary(true);
        $user->setDeputyUid($deputyUid);
        $user->setRegistrationRoute($registrationType);
    }

    private function populateClient(Client $client, SelfRegisterData $selfRegisterData): void
    {
        $client->setFirstname($selfRegisterData->getClientFirstname() ?? '');
        $client->setLastname($selfRegisterData->getClientLastname() ?? '');
        $client->setCaseNumber($selfRegisterData->getCaseNumber() ?? '');
    }
}
