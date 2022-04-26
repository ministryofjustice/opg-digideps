<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Authenticator that reads "AuthToken" token in request
 * and uses UserByTokenProvider to get the user from that value.
 */
class HeaderTokenAuthenticator extends AbstractAuthenticator
{
    public const HEADER_NAME = 'AuthToken';

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
//    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
//    {
//        if ($userProvider instanceof RedisUserProvider) {
//            $authTokenValue = $token->getCredentials();
//            $user = $userProvider->loadUserByUsername($authTokenValue);
//
//            return new PreAuthenticatedToken(
//                $user,
//                $authTokenValue,
//                $providerKey,
//                $user->getRoles()
//            );
//        } else {
//            throw new \InvalidArgumentException('The user provider must be an instance '
//            . 'of UserByTokenProvider (' . get_class($userProvider) . ' was given).');
//        }
//    }

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
        // TODO: Do below but for token in header. We also need an authenticator for first login requests but check to see if we always include AuthToken in header and then guard against it being the secret string (e.g. don't use this authenticator if its the secret string)
        dd('api headertokenauth authenticate');

        $token = $request->headers->get(self::HEADER_NAME);

        return new Passport(
            new UserBadge($token),
            new CustomCredentials(function ($credentials, User $user) {
                dd($credentials, $user);
            }, $token)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // TODO: Implement onAuthenticationSuccess() method.
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // TODO: Implement onAuthenticationFailure() method.
    }
}
