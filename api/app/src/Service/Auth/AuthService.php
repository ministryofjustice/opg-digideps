<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JWT\JWTService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AuthService
{
    public const HEADER_CLIENT_SECRET = 'ClientSecret';
    public const HEADER_JWT = 'JWT';
    private array $clientSecrets = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserRepository $userRepository,
        private readonly RoleHierarchyInterface $roleHierarchy,
        private array $clientPermissions,
        private readonly JWTService $JWTService,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        if (empty($clientPermissions)) {
            throw new \InvalidArgumentException('client_permissions not defined in config.');
        }

        $this->clientSecrets = [
            'admin' => getenv('SECRETS_ADMIN_KEY'),
            'frontend' => getenv('SECRETS_FRONT_KEY'),
        ];
    }

    public function isSecretValid(Request $request): bool
    {
        $clientSecretFromRequest = $request->headers->get(self::HEADER_CLIENT_SECRET);

        if (!is_string($clientSecretFromRequest)) {
            return false;
        }

        return in_array($clientSecretFromRequest, $this->clientSecrets, true);
    }

    /**
     * @return User|bool|null or null if the user it not found or password is wrong
     */
    public function getUserByEmailAndPassword($email, $pass): User|bool|null
    {
        if (!$email || !$pass) {
            return null;
        }
        // get user by email
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (!$user instanceof User) {
            $this->logger->info('Login: user by email not found ');

            return false;
        }

        if ($this->passwordHasher->isPasswordValid($user, $pass)) {
            return $user;
        }

        $this->logger->info('Login: password mismatch');

        return null;
    }

    public function getUserByToken($token): User|null
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
            'registrationToken' => $token,
        ]);

        return $user;
    }

    public function isSecretValidForRole(?string $roleName, Request $request): bool
    {
        if (is_null($roleName)) {
            return false;
        }

        $clientSecretFromRequest = $request->headers->get(self::HEADER_CLIENT_SECRET);

        if (!is_string($clientSecretFromRequest)) {
            return false;
        }

        $clientSource = array_search($clientSecretFromRequest, $this->clientSecrets);

        $permittedRoles = isset($this->clientPermissions[$clientSource]) ?
            $this->clientPermissions[$clientSource] : [];

        // Get all roles available to this user
        $availableRoles = $this->roleHierarchy->getReachableRoleNames([$roleName]);
        foreach ($availableRoles as $role) {
            if (in_array($role, $permittedRoles)) {
                return true;
            }
        }

        return false;
    }

    public function JWTIsValid(Request $request): bool
    {
        $jwt = $request->headers->get(self::HEADER_JWT);

        if (!is_null($jwt)) {
            return $this->JWTService->verify($jwt);
        }

        return false;
    }
}
