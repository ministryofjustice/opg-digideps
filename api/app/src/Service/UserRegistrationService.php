<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Model\SelfRegisterData;
use App\Repository\ClientRepository;
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
     * - throw error 422 if user email is already found
     * - throw error 462 if deputy could not be uniquely identified.
     *
     * @see PreRegistrationVerificationService::validate() for other error codes (460, 461 etc.)
     *
     * @throws \RuntimeException with one of the error codes above if self-registration failed
     */
    public function selfRegisterUser(SelfRegisterData $selfRegisterData): User
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->em->getRepository(User::class);

        // check the user doesn't already exist
        $existingUser = $userRepo->findOneBy(['email' => $selfRegisterData->getEmail()]);
        if ($existingUser) {
            $message = sprintf('User with email %s already exists.', $existingUser->getEmail());
            throw new \RuntimeException(json_encode($message) ?: '', 422);
        }

        // validate user-supplied registration data against the pre-registration table;
        // if validation fails, this throws a runtime exception which propagates to callers of this method
        $preregMatches = $this->preRegistrationVerificationService->validate(
            $selfRegisterData->getCaseNumber(),
            $selfRegisterData->getClientLastname(),
            $selfRegisterData->getFirstname(),
            $selfRegisterData->getLastname(),
            $selfRegisterData->getPostcode()
        );

        // fail if a deputy could not be uniquely identified due to user-supplied registration data matching more than one
        // client + deputy combination
        if (1 !== count($preregMatches)) {
            $message = sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber());
            throw new \RuntimeException(json_encode($message) ?: '', 462);
        }

        // create a new client if there isn't one already
        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->em->getRepository(Client::class);

        $client = $clientRepo->findByCaseNumber($selfRegisterData->getCaseNumber() ?? '');
        if (is_null($client)) {
            $client = new Client();
            $this->populateClient($client, $selfRegisterData);
        }

        // create the new user
        $user = new User();
        $user->setDeputyNo($preregMatches[0]->getDeputyUid());
        $user->setDeputyUid(intval($preregMatches[0]->getDeputyUid()));

        $this->populateUser($user, $selfRegisterData);
        $user->recreateRegistrationToken();
        $user->setPreRegisterValidatedDate(new \DateTime('now'));
        $user->setRegistrationRoute(User::SELF_REGISTER);

        if (!$this->preRegistrationVerificationService->deputyUidHasOtherUserAccounts($preregMatches[0]->getDeputyUid())) {
            $user->setIsPrimary(true);
        }

        $this->saveUserAndClient($user, $client);

        return $user;
    }

    /**
     * @return PreRegistration[]
     *
     * @throws \RuntimeException
     */
    public function validateCoDeputy(SelfRegisterData $selfRegisterData): string
    {
        /** @var UserRepository $repo */
        $repo = $this->em->getRepository(User::class);

        $user = $repo->findOneBy(['email' => $selfRegisterData->getEmail()]);
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

        if (1 !== count($preregMatches)) {
            // a deputy could not be uniquely identified due to matching first name, last name and postcode across more than one deputy record
            $message = sprintf('A unique deputy record for case number %s could not be identified', $selfRegisterData->getCaseNumber());
            throw new \RuntimeException(json_encode($message) ?: '', 462);
        }

        // check if it's the primary account for the co-deputy
        $coDeputyUid = $preregMatches[0]->getDeputyUid();
        $existingDeputyCases = $this->em->getRepository(Client::class)->hasExistingDeputyCase($selfRegisterData->getCaseNumber(), $coDeputyUid);
        if ($existingDeputyCases) {
            $message = sprintf('A deputy with deputy number %s is already associated with the case number %s', $coDeputyUid, $selfRegisterData->getCaseNumber());
            throw new \RuntimeException(json_encode($message) ?: '', 463);
        }

        // no exceptions thrown, so return the matched deputy UID
        return $coDeputyUid;
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
