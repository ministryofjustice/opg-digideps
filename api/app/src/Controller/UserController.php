<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as SecurityHelper;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/user')]
class UserController extends RestController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly ClientRepository $clientRepository,
        private readonly UserVoter $userVoter,
        private readonly SecurityHelper $securityHelper,
        private readonly EntityManagerInterface $em,
        private readonly AuthService $authService,
        private readonly RestFormatter $formatter,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
        parent::__construct($em);
    }

    #[Route(path: '', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_ORG_NAMED') or is_granted('ROLE_ORG_ADMIN')"))]
    public function add(Request $request): User
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'role_name' => 'notEmpty',
            'email' => 'notEmpty',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        $newUser = new User();
        $newUser->populate($data);

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $this->userService->addUser($loggedInUser, $newUser, null);

        $groups = $request->query->has('groups') ?
            $request->query->all('groups') : ['user', 'user-teams', 'team'];
        $this->formatter->setJmsSerialiserGroups($groups);

        return $newUser;
    }

    #[Route(path: '/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): array
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if (
            $loggedInUser->getId() != $requestedUser->getId()
            && !$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$this->isGranted(User::ROLE_ORG_NAMED)
            && !$this->isGranted(User::ROLE_ORG_ADMIN)
        ) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        $originalUser = clone $requestedUser;

        $data = $this->formatter->deserializeBodyContent($request);
        $requestedUser->populate($data);

        // check if rolename in data - if so add audit log
        $this->userService->editUser($originalUser, $requestedUser);

        if (null !== $requestedUser->getRegistrationToken()) {
            $requestedUser->setRegistrationToken(null);
            $this->em->persist($requestedUser);
            $this->em->flush();
        }

        return ['id' => $requestedUser->getId()];
    }

    #[Route(path: '/{id}/is-password-correct', methods: ['POST'])]
    public function isPasswordCorrect(Request $request, int $id): bool
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if ($loggedInUser->getId() != $requestedUser->getId()) {
            throw $this->createAccessDeniedException("Not authorised to check other user's password");
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'password' => 'notEmpty',
        ]);

        return $this->hasherFactory->getPasswordHasher($loggedInUser)->verify($requestedUser->getPassword(), $data['password']);
    }

    /**
     * See RegistrationTokenAuthenticator for checks and how User is set in session.
     */
    #[Route(path: '/{id}/set-password', methods: ['PUT'])]
    public function changePassword(Request $request, int $id): int
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'password' => 'notEmpty',
        ]);

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if (!$requestedUser->getActive() && isset($data['token'])) {
            if ($requestedUser->getRegistrationToken() !== $data['token']) {
                $tokenMismatchMessage = sprintf('Registration token provided does not match the User (id: %s) registration token', $id);
                throw $this->createAccessDeniedException($tokenMismatchMessage);
            }
        } else {
            /** @var User $loggedInUser */
            $loggedInUser = $this->getUser();

            if (is_null($loggedInUser)) {
                throw $this->createAccessDeniedException('A user is not set in session - ensure a user has been set before calling set-password');
            }

            if ($loggedInUser->getId() != $requestedUser->getId()) {
                throw $this->createAccessDeniedException("Not authorised to change other user's data");
            }
        }

        $newPassword = $this->passwordHasher->hashPassword($requestedUser, $data['password']);

        $requestedUser->setPassword($newPassword);

        $this->em->persist($requestedUser);
        $this->em->flush();

        return $requestedUser->getId();
    }

    /**
     * change email.
     */
    #[Route(path: '/{id}/update-email', methods: ['PUT'])]
    public function changeEmail(Request $request, $id): int
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if ($loggedInUser->getId() != $requestedUser->getId()) {
            throw $this->createAccessDeniedException("Not authorised to change other user's data");
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'updated_email' => 'notEmpty',
        ]);

        $requestedUser->setEmail($data['updated_email']);

        $this->em->flush();

        return $requestedUser->getId();
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getOneById(Request $request, int $id): ?User
    {
        return $this->getOneByFilter($request, 'user_id', $id);
    }

    #[Route(path: '/get-one-by/{what}/{filter}', requirements: ['what' => '(user_id|email|case_number)'], methods: ['GET'])]
    public function getOneByFilter(Request $request, string $what, $filter): ?User
    {
        if ('email' == $what) {
            /** @var ?User $user */
            $user = $this->userRepository->findOneBy(['email' => strtolower($filter)]);
            if (!$user) {
                throw new \RuntimeException('User not found', 404);
            }
        } elseif ('case_number' == $what) {
            /** @var ?Client $client */
            $client = $this->clientRepository->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new \RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new \RuntimeException('Client has not users', 404);
            }
            /** @var User $user */
            $user = $client->getUsers()[0];
        } elseif ('user_id' == $what) {
            /** @var ?User $user */
            $user = $this->userRepository->find($filter);
            if (!$user) {
                throw new \RuntimeException('User not found', 419);
            }
        } else {
            throw new \RuntimeException('wrong query', 500);
        }

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $groups = $request->query->has('groups') ?
            $request->query->all('groups') : ['user'];

        $this->formatter->setJmsSerialiserGroups($groups);

        if ($loggedInUser->isCoDeputyWith($user)) {
            return $user;
        }

        $requestedUserIsLogged = $loggedInUser->getId() == $user->getId();

        // only allow admins to access any user, otherwise the user can only see himself
        if (
            !$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$requestedUserIsLogged
        ) {
            if (
                $this->isGranted(User::ROLE_PA_ADMIN)
                || $this->isGranted(User::ROLE_PROF_ADMIN)
                || $this->isGranted(User::ROLE_PA_NAMED)
                || $this->isGranted(User::ROLE_PROF_NAMED)
            ) {
                $this->denyAccessUnlessGranted('delete-user', $user, 'Access denied');
            } else {
                throw $this->createAccessDeniedException("Not authorised to see other user's data");
            }
        }

        return $user;
    }

    /**
     * Get user by email, and retrieve only id and team names the user belongs to.
     * Only for ROLE_PROF named and admin, when adding users to multiple teams.
     * Returns empty if user doesn't exist.
     */
    #[Route(path: '/get-team-names-by-email/{email}', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ORG_NAMED') or is_granted('ROLE_ORG_ADMIN')"))]
    public function getUserTeamNames(string $email): ?User
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $this->formatter->setJmsSerialiserGroups(['user-id', 'team-names']);

        return $user;
    }

    /**
     * Delete user with clients.
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_ADMIN_MANAGER')]
    public function delete(int $id): array
    {
        /** @var ?User $deletee */
        $deletee = $this->userRepository->find($id);

        $token = $this->securityHelper->getToken();

        if (is_null($token)) {
            throw $this->createAccessDeniedException('Cannot delete, as no token could be retrieved');
        }

        $canDelete = $this->userVoter->vote($token, $deletee, [UserVoter::DELETE_USER]);

        if (UserVoter::ACCESS_DENIED === $canDelete) {
            /** @var ?User $user */
            $user = $token->getUser();

            if (is_null($user)) {
                throw $this->createAccessDeniedException('Cannot delete user, as token has no associated user to do the deletion');
            }

            $errMessage = sprintf('A %s cannot delete a %s', $user->getRoleName(), $deletee?->getRoleName() ?? 'UNKNOWN');
            throw $this->createAccessDeniedException($errMessage);
        }

        if (!is_null($deletee)) {
            $this->em->remove($deletee);
            $this->em->flush();
        }

        return [];
    }

    #[Route(path: '/get-all', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function getAll(Request $request): ?array
    {
        $this->formatter->setJmsSerialiserGroups(['user']);

        return $this->userRepository->findUsersByQueryParameters($request);
    }

    /**
     * Requires client secret.
     */
    #[Route(path: '/recreate-token/{email}', defaults: ['email' => 'none'], methods: ['PUT'])]
    public function recreateToken(Request $request, string $email): User
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /** @var User $user */
        $user = $this->findEntityBy(User::class, ['email' => strtolower($email)]);
        $hasAdminSecret = $this->authService->isSecretValidForRole(User::ROLE_ADMIN, $request);

        if (!$hasAdminSecret && User::ROLE_ADMIN == $user->getRoleName()) {
            throw new \RuntimeException('Admin emails not accepted.', 403);
        }

        $user->recreateRegistrationToken();

        $this->em->flush($user);

        $this->formatter->setJmsSerialiserGroups(['user']);

        return $user;
    }

    #[Route(path: '/get-by-token/{token}', methods: ['GET'])]
    public function getByToken(Request $request, string $token): User
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException($user->getRoleName().' user role not allowed from this client.', 403);
        }

        // `user-login` contains number of clients and reports, needed to properly redirect the user to the right page after activation
        $this->formatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    #[Route(path: '/agree-terms-use/{token}', methods: ['PUT'])]
    public function agreeTermsUse(Request $request, string $token): int
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException($user->getRoleName().' user role not allowed from this client.', 403);
        }

        $user->setAgreeTermsUse(true);

        $this->em->persist($user);
        $this->em->flush($user);

        return $user->getId();
    }

    #[Route(path: '/clear-registration-token/{token}', methods: ['PUT'])]
    public function clearRegistrationToken(Request $request, string $token): int
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException($user->getRoleName().' user role not allowed from this client.', 403);
        }

        $user->setRegistrationToken(null);

        $this->em->persist($user);
        $this->em->flush($user);

        return $user->getId();
    }

    #[Route(path: '/{id}/team', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ORG')]
    public function getTeamByUserId(Request $request, int $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User|null $requestedUser */
        $requestedUser = $this->userRepository->find($id);

        if (!$requestedUser) {
            throw new \RuntimeException('User not found', 419);
        }

        /** @var ArrayCollection $requestedUserTeams */
        $requestedUserTeams = $requestedUser->getTeams();

        /** @var ArrayCollection $loggedInUserTeams */
        $loggedInUserTeams = $loggedInUser->getTeams();
        if ($requestedUserTeams->first() !== $loggedInUserTeams->first()) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        $groups = $request->query->has('groups') ?
            $request->query->all('groups') : ['team', 'team-users', 'user'];

        $this->formatter->setJmsSerialiserGroups($groups);

        return $requestedUserTeams->first();
    }

    /**
     * Endpoint for getting a reg token for user.
     *
     * @throws \Exception
     */
    #[Route(path: '/get-reg-token', methods: ['GET'])]
    public function getRegToken(): string
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->recreateRegistrationToken();
        $user->setRegistrationToken($user->getRegistrationToken());

        $this->em->persist($user);
        $this->em->flush();

        return $user->getRegistrationToken() ?? '';
    }

    /**
     * Set Registration date on user.
     */
    #[Route(path: '/{id}/set-registration-date', methods: ['PUT'])]
    public function setRegistrationDate(Request $request, int $id): int
    {
        $data = $this->formatter->deserializeBodyContent($request);

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if (!$requestedUser->getActive() && isset($data['token'])) {
            if ($requestedUser->getRegistrationToken() !== $data['token']) {
                $tokenMismatchMessage = sprintf('Registration token provided does not match the User (id: %s) registration token', $id);
                throw $this->createAccessDeniedException($tokenMismatchMessage);
            }
        }

        $requestedUser->setRegistrationDate(new \DateTime());

        $this->em->flush();

        return $requestedUser->getId();
    }

    /**
     * Set active flag on user.
     */
    #[Route(path: '/{id}/set-active', methods: ['PUT'])]
    public function setActive(Request $request, int $id): int
    {
        $data = $this->formatter->deserializeBodyContent($request);

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if (!$requestedUser->getActive() && isset($data['token'])) {
            if ($requestedUser->getRegistrationToken() !== $data['token']) {
                $tokenMismatchMessage = sprintf('Registration token provided does not match the User (id: %s) registration token', $id);
                throw $this->createAccessDeniedException($tokenMismatchMessage);
            }
        }

        $requestedUser->setActive(true);

        $this->em->flush();

        return $requestedUser->getId();
    }

    /**
     * Endpoint for getting the primary user account for user.
     * Returns null if the user has no or multiple primary account(s).
     *
     * @throws \Exception
     */
    #[Route(path: '/get-primary-email/{deputyUid}', methods: ['GET'])]
    public function getPrimaryEmail(int $deputyUid): ?string
    {
        $users = $this->userRepository->findBy(['deputyUid' => $deputyUid, 'isPrimary' => true]);

        // multiple primary accounts or no primary account
        if (1 !== count($users)) {
            return null;
        }

        return $users[0]->getEmail();
    }

    /**
     * Endpoint for getting the primary user account associated with a deputy uid.
     *
     * @throws \Exception
     */
    #[Route(path: '/get-primary-user-account/{deputyUid}', methods: ['GET'])]
    public function getPrimaryUserAccount(int $deputyUid): ?User
    {
        $this->formatter->setJmsSerialiserGroups(['user', 'user-list']);

        return $this->userRepository->findPrimaryUserByDeputyUid($deputyUid);
    }
}
