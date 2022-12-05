<?php

namespace App\Security;

use App\Entity\User;
use App\Exception\UserWrongCredentialsException;
use App\Repository\UserRepository;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

/**
 * Authenticator that reads "AuthToken" token in request
 * and uses UserByTokenProvider to get the user from that value.
 */
class HeaderTokenAuthenticator extends AbstractAuthenticator
{
    public const HEADER_NAME = 'AuthToken';

    public function __construct(
        private Client $redis,
        private UserRepository $userRepository,
        private LoggerInterface $logger
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER_NAME) && !empty($request->headers->get(self::HEADER_NAME));
    }

    public function authenticate(Request $request)
    {
        $authTokenKey = $request->headers->get(self::HEADER_NAME);

        $redisToken = $this->redis->get($authTokenKey);
        if (!$redisToken) {
            $this->logger->warning(sprintf('Auth token not found in Redis with key %s', $authTokenKey));
            throw new UserNotFoundException('User not found');
        }

        /** @var PostAuthenticationToken $postAuthToken */
        $postAuthToken = unserialize($redisToken);

        if (!$postAuthToken) {
            $this->logger->warning(sprintf('Could not deserialize token with key %s', $authTokenKey));
            throw new UserNotFoundException('User not found');
        }

        return new SelfValidatingPassport(
            new UserBadge($postAuthToken->getUserIdentifier(), function ($userEmail) {
                $user = $this->userRepository->findOneBy(['email' => strtolower($userEmail)]);

                if ($user instanceof User) {
                    return $user;
                }

                throw new UserNotFoundException('User not found');
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new UserWrongCredentialsException($exception->getMessage(), 419);
    }
}
