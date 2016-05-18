<?php

namespace AppBundle\Service\Auth;

use MockeryStub as m;
use Symfony\Component\HttpFoundation\Request;

class HeaderTokenAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HeaderTokenAuthenticator
     */
    private $headerTokenAuth;

    public function setUp()
    {
        $this->headerTokenAuth = new HeaderTokenAuthenticator();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testcreateTokenNotFound()
    {
        $request = new Request();

        $this->headerTokenAuth->createToken($request, 'providerKey');
    }

    public function testcreateToken()
    {
        $request = new Request();
        $request->headers->set(HeaderTokenAuthenticator::HEADER_NAME, 'AuthTokenValue');

        $preAuthToken = $this->headerTokenAuth->createToken($request, 'providerKey');
        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $preAuthToken);
        $this->assertEquals('anon.', $preAuthToken->getUser());
        $this->assertEquals('providerKey', $preAuthToken->getProviderKey());
        $this->assertEquals('AuthTokenValue', $preAuthToken->getCredentials());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testauthenticateTokenWrongProvider()
    {
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = m::mock('Symfony\Component\Security\Core\User\UserProviderInterface');

        $this->headerTokenAuth->authenticateToken($token, $user, 'providerKey');
    }

    public function testauthenticateTokenSuccess()
    {
        $user = m::stub('AppBundle\Entity\User', [
                'getRoles' => ['role1'],
        ]);

        $token = m::stub('Symfony\Component\Security\Core\Authentication\Token\TokenInterface', [
            'getCredentials' => 'AuthTokenValue',
        ]);
        $userProvider = m::stub('AppBundle\Service\Auth\UserProvider', [
            'loadUserByUsername(AuthTokenValue)' => $user,
        ]);

        $preAuthToken = $this->headerTokenAuth->authenticateToken($token, $userProvider, 'providerKey');
        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $preAuthToken);
        $this->assertEquals($user, $preAuthToken->getUser());
        $this->assertEquals('providerKey', $preAuthToken->getProviderKey());
        $this->assertEquals('AuthTokenValue', $preAuthToken->getCredentials());
    }

    public function testsupportsToken()
    {
        $token = m::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $preAuthToken = m::stub('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', [
            'getProviderKey' => 'providerKey',
        ]);

        $this->assertFalse($this->headerTokenAuth->supportsToken($token, 'providerKey'));
        $this->assertFalse($this->headerTokenAuth->supportsToken($preAuthToken, 'providerKey-WRONG'));
        $this->assertTrue($this->headerTokenAuth->supportsToken($preAuthToken, 'providerKey'));
    }

    public function tearDown()
    {
        m::close();
    }
}
