<?php

namespace App\Security;

use App\Entity\User;
use App\Exception\UserWrongCredentialsException;
use App\Repository\UserRepository;
use Predis\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
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
//    public static function getTokenFromRequest(Request $request)
//    {
//        return $request->headers->get(self::HEADER_NAME);
//    }

//    public function createToken(Request $request, $providerKey)
//    {
//        // look for an apikey query parameter
//        $authTokenValue = self::getTokenFromRequest($request);
//        // or if you want to use an "apikey" header, then do something like this:
//        // $apiKey = $request->headers->get('apikey');
//
//        if (!$authTokenValue) {
//            throw new \RuntimeException('No Token found in Request Headers', 401);
//        }
//
//        $credentials = $authTokenValue;
//
//        return new PreAuthenticatedToken(
//            'anon.',
//            $credentials,
//            $providerKey
//        );
//    }

    /**
     * Called at each request.
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($userProvider instanceof RedisUserProvider) {
            $authTokenValue = $token->getCredentials();
            $user = $userProvider->loadUserByUsername($authTokenValue);

            return new PreAuthenticatedToken(
                $user,
                $authTokenValue,
                $providerKey,
                $user->getRoles()
            );
        } else {
            throw new \InvalidArgumentException('The user provider must be an instance '.'of UserByTokenProvider ('.get_class($userProvider).' was given).');
        }
    }

//    public function supportsToken(TokenInterface $token, $providerKey)
//    {
//        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
//    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER_NAME) && !empty($request->headers->get(self::HEADER_NAME));
    }

    public function authenticate(Request $request)
    {
        $authTokenKey = $request->headers->get(self::HEADER_NAME);
        /** @var PostAuthenticationToken $postAuthToken */
        $postAuthToken = unserialize($this->redis->get($authTokenKey));

        if (!$postAuthToken) {
            $this->logger->warning(sprintf('Auth token not found in Redis with key %s', $authTokenKey));
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
