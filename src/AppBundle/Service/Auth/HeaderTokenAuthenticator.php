<?php

namespace AppBundle\Service\Auth;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use AppBundle\Entity\User;

/**
 * Authenticator that reads "AuthToken" token in request
 * and uses UserByTokenProvider to get the user from that value
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
            throw new BadCredentialsException('No Token found in Request Headers');
        }

        $credentials = $authTokenValue;

        return new PreAuthenticatedToken(
            'anon.', $credentials, $providerKey
        );
    }


    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if ($userProvider instanceof UserProviders\UserByTokenProviderInterface) {
            $authTokenValue = $token->getCredentials();
            $user = $userProvider->loadUserByUsername($authTokenValue);

            return new PreAuthenticatedToken(
                $user, $authTokenValue, $providerKey, $user->getRoles()
            );
        } else {
            throw new \InvalidArgumentException('The user provider must be an instance '
            . 'of UserByTokenProvider (' . get_class($userProvider) . ' was given).');
        }
    }


    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

}