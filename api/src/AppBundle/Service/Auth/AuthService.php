<?php

namespace AppBundle\Service\Auth;

use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AuthService
{
    const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $clientSecrets;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EncoderFactoryInterface
     */
    private $securityEncoderFactory;

    /**
     * @var RoleHierarchyInterface
     */

    private $roleHierarchy;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     * @param LoggerInterface $logger
     * @param ContainerInterface $container
     * @param UserRepository $userRepository
     * @param RoleHierarchyInterface $roleHierarchy
     * @param array $clientSecrets
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        LoggerInterface $logger,
        ContainerInterface $container,
        UserRepository $userRepository,
        RoleHierarchyInterface $roleHierarchy,
        array $clientSecrets
    )
    {
        $this->clientSecrets = $clientSecrets;

        if (!is_array($this->clientSecrets) || empty($this->clientSecrets)) {
            throw new \InvalidArgumentException('client_secrets not defined in config.');
        }

        $this->container = $container;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->securityEncoderFactory = $encoderFactory;
        $this->roleHierarchy = $roleHierarchy;
        $this->clientSecrets = $clientSecrets;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isSecretValid(Request $request)
    {
        $clientSecretFromRequest = $request->headers->get(self::HEADER_CLIENT_SECRET);

        if (!is_string($clientSecretFromRequest)) {
            return false;
        }

        return isset($this->clientSecrets[$clientSecretFromRequest]);
    }

    /**
     * @param string $email
     * @param string $pass
     *
     * @return User|bool|null or null if the user it not found or password is wrong
     */
    public function getUserByEmailAndPassword($email, $pass)
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

        // check hashed password matching
        $encodedPass = $this->securityEncoderFactory->getEncoder($user)
            ->encodePassword($pass, $user->getSalt());

        if ($user->getPassword() == $encodedPass) {
            return $user;
        }

        $this->logger->info('Login: password mismatch');

        return null;
    }

    /**
     * @param string $token
     *
     * @return User|null
     */
    public function getUserByToken($token)
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([
            'registrationToken' => $token,
        ]);

        return $user;
    }

    /**
     * @param string  $roleName
     * @param Request $request
     *
     * @return bool
     */
    public function isSecretValidForRole($roleName, Request $request)
    {
        $clientSecretFromRequest = $request->headers->get(self::HEADER_CLIENT_SECRET);

        if (!is_string($clientSecretFromRequest)) {
            return false;
        }

        $allowedRoles = isset($this->clientSecrets[$clientSecretFromRequest]['permissions']) ?
            $this->clientSecrets[$clientSecretFromRequest]['permissions'] : [];

        // also allow inherited roles
        $hierarchy = $this->roleHierarchy->getReachableRoles($allowedRoles);
        foreach ($hierarchy as $cr => $parents) { // ROLE_PA_NAMED => [ROLE_PA]
            foreach ($parents as $parent) {
                if (in_array($parent, $allowedRoles)) {
                    $allowedRoles[] = $cr; //ROLE_PA_NAMED
                }
            }
        }

        return in_array($roleName, $allowedRoles);
    }
}
