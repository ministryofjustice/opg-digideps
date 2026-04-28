<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired.
 *
 * @implements UserProviderInterface<User>
 */
class RedisUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private readonly int $timeoutSeconds;

    public function __construct(
        private readonly Client $redis,
        private readonly LoggerInterface $logger,
        readonly array $options,
        private readonly UserRepository $userRepository,
        private readonly string $workspace
    ) {
        $this->timeoutSeconds = (int) ($options['timeout_seconds'] ?? 30);
    }

    public function generateRandomTokenAndStore(User $user): string
    {
        $token = $this->workspace . '_' . $user->getId() . '_' . sha1(microtime() . spl_object_hash($user) . rand(1, 999));

        $this->redis->set($token, $user->getId());
        $this->redis->expire($token, $this->timeoutSeconds);

        return $token;
    }

    public function removeToken($token): void
    {
        $this->redis->set($token, null);
    }

    public function refreshUser(UserInterface $user)
    {
        // TODO: Implement refreshUser() method.
        throw new \RuntimeException('Not implemented');
    }

    public function supportsClass(string $class): bool
    {
        return 'OPG\Digideps\Backend\Entity\User' === $class || is_subclass_of($class, 'OPG\Digideps\Backend\Entity\User');
    }

    public function loadUserByUsername(string $username): ?User
    {
        $token = $username;

        $userId = $this->redis->get($token);
        if (!$userId) {
            throw new \RuntimeException("Token $username expired", 419);
        }

        /** @var User|null $user */
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->logger->warning("user $userId not found");
            throw new \RuntimeException('User associated to token not found', 419);
        }

        // refresh token creation time
        $this->redis->expire($token, $this->timeoutSeconds);

        return $user;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method UserInterface loadUserByIdentifier(string $identifier)
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // TODO: Implement upgradePassword() method.
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // TODO: Implement loadUserByIdentifier() method.
        throw new \RuntimeException('Not implemented');
    }
}
