<?php

namespace AppBundle\Service\Auth;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Authenticator that reads "AuthToken" token in request
 * and uses UserByTokenProvider to get the user from that value.
 */
class HeaderTokenAuthenticator implements SimplePreAuthenticatorInterface
{
    const HEADER_NAME = 'AuthToken';

    public static function getTokenFromRequest(Request $request)
    {
        return $request->headers->get(self::HEADER_NAME);
    }

    public function createToken(Request $request, $providerKey)
    {
        // look for an apikey query parameter
        $authTokenValue = self::getTokenFromRequest($request);
        // or if you want to use an "apikey" header, then do something like this:
        // $apiKey = $request->headers->get('apikey');

        if (!$authTokenValue) {
            throw new \RuntimeException('No Token found in Request Headers', 401);
        }

        $credentials = $authTokenValue;

        return new PreAuthenticatedToken(
            'anon.', $credentials, $providerKey
        );
    }

    /**
     * Called at each request.
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($userProvider instanceof UserProvider) {
            $authTokenValue = $token->getCredentials();
            $user = $userProvider->loadUserByUsername($authTokenValue);

            return new PreAuthenticatedToken(
                $user, $authTokenValue, $providerKey, $user->getRoles()
            );
        } else {
            throw new \InvalidArgumentException('The user provider must be an instance '
            .'of UserByTokenProvider ('.get_class($userProvider).' was given).');
        }
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}
