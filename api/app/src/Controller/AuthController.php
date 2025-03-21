<?php

namespace App\Controller;

use App\Entity\User;
use App\EventListener\RestInputOuputFormatter;
use App\Security\HeaderTokenAuthenticator;
use App\Security\RedisUserProvider;
use App\Service\Formatter\RestFormatter;
use App\Service\JWT\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Route(path: '/auth')]
class AuthController extends RestController
{
    public function __construct(
        private readonly RestFormatter $restFormatter,
        private readonly JWTService $JWTService,
        private readonly LoggerInterface $logger,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly string $workspace,
        EntityManagerInterface $em,
    ) {
       parent::__construct($em);
    }

    /**
     *
     * @return User
     * @throws \Throwable
     */
    #[Route(path: '/login', methods: ['POST'], name: 'api_login')]
    public function login(
        RestInputOuputFormatter $restInputOutputFormatter,
        EntityManagerInterface $em,
        Client $redis,
    ) {
        try {
            // See LoginRequestAuthenticator and RegistrationTokenAuthenticator for checks. User is set in token storage on successful authentication via Symfony event
            $token = $this->tokenStorage->getToken();

            if (null !== $token) {
                /** @var User $user */
                $user = $token->getUser();

                $user->setLastLoggedIn(new \DateTime());
                $em->persist($user);
                $em->flush();

                // Now doing this inline rather than injecting RedisUserProvider
                $authToken = $this->workspace.'_'.$user->getId().'_'.sha1(microtime().spl_object_hash($user).rand(1, 999));
                $redis->set($authToken, serialize($this->tokenStorage->getToken()));

                // add token into response
                $restInputOutputFormatter->addResponseModifier(function ($response) use ($authToken): void {
                    $response->headers->set(HeaderTokenAuthenticator::HEADER_NAME, $authToken);
                });
            } else {
                throw new \Exception('User token is not available');
            }

            if (User::ROLE_SUPER_ADMIN === $user->getRoleName()) {
                $jwt = $this->JWTService->createNewJWT($user);

                $restInputOutputFormatter->addResponseModifier(function ($response) use ($jwt): void {
                    $response->headers->set('JWT', $jwt);
                });
            }

            // needed for redirector
            $this->restFormatter->setJmsSerialiserGroups(['user', 'user-login']);

            return $user;
        } catch (\Throwable $e) {
            $this->logger->warning(sprintf('Error when attempting to log user in: %s', $e->getMessage()));
            throw $e;
        }
    }

    #[Route(path: '/logout', methods: ['POST'])]
    public function logout(RedisUserProvider $userProvider)
    {
        $authToken = $this->tokenStorage->getToken();

        return $userProvider->removeToken($authToken);
    }

    /**
     * Test endpoint used for testing to check auth permissions.
     */
    #[Route(path: '/get-logged-user', methods: ['GET'])]
    public function getLoggedUser()
    {
        $this->restFormatter->setJmsSerialiserGroups(['user', 'user-login']);
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user) {
            $this->logger->warning('Expected to find a token in tokenStorage but it was empty');
            throw new UserNotFoundException();
        }

        return $user;
    }
}
