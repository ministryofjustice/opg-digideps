<?php

namespace AppBundle\Service\Auth;

use AppBundle\Entity\User;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class AuthService
{
    const HEADER_CLIENT_SECRET = 'ClientSecret';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->clientSecrets = $container->getParameter('client_secrets');
        if (!is_array($this->clientSecrets) || empty($this->clientSecrets)) {
            throw new \InvalidArgumentException('client_secrets not defined in config.');
        }
        $this->container = $container;
        $this->userRepo = $container->get('em')->getRepository('AppBundle\Entity\User');
        $this->logger = $container->get('logger');
        $this->securityEncoderFactory = $container->get('security.encoder_factory');
    }

    /**
     * @return array
     */
    public function isSecretValid(Request $request)
    {
        $clientSecretFromRequest = $request->headers->get(self::HEADER_CLIENT_SECRET);

        return isset($this->clientSecrets[$clientSecretFromRequest]);
    }

    /**
     * @param string $email
     * @param string $pass
     *
     * @return User or null if the user it not found or password is wrong
     */
    public function getUserByEmailAndPassword($email, $pass)
    {
        if (!$email || !$pass) {
            return;
        }
         // get user by email
        $user = $this->userRepo->findOneBy([
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

        return;
    }

    /**
     * @param string $token
     *
     * @return User|null
     */
    public function getUserByToken($token)
    {
        return $this->userRepo->findOneBy([
            'registrationToken' => $token,
        ]) ?: null;
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
        $allowedRoles = isset($this->clientSecrets[$clientSecretFromRequest]['permissions']) ?
            $this->clientSecrets[$clientSecretFromRequest]['permissions'] : [];

        // also allow inherited roles
        $hierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        foreach($hierarchy as $cr => $parents) { // ROLE_PA_NAMED => [ROLE_PA]
            foreach($parents as $parent) {
                if (in_array($parent, $allowedRoles) ) {
                    $allowedRoles[] = $cr; //ROLE_PA_NAMED
                }
            }
        }


        return in_array($roleName, $allowedRoles);
    }

}
