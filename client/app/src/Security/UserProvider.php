<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\Client\Internal\UserApi;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(private UserApi $userApi)
    {
    }

    public function refreshUser(UserInterface $user)
    {
        $user = $this->userApi->get($user->getId(), ['user', 'user-organisations']);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier)
    {
        $user = $this->userApi->getByEmail($identifier, ['user', 'user-organisations']);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        return $user;
    }

    public function loadUserByUsername(string $username)
    {
        throw new \RuntimeException('Method should not be called, and removed after symfony 6 upgrade');
    }
}
