<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\HeaderTokenAuthenticator;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class HeaderTokenAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var HeaderTokenAuthenticator
     */
    private $sut;
    private ObjectProphecy|Client $redisClient;
    private ObjectProphecy|UserRepository $userRepository;
    private ObjectProphecy|LoggerInterface $logger;

    public function setUp(): void
    {
        $this->redisClient = self::prophesize(Client::class);
        $this->userRepository = self::prophesize(UserRepository::class);
        $this->logger = self::prophesize(LoggerInterface::class);

        $this->sut = new HeaderTokenAuthenticator(
            $this->redisClient->reveal(),
            $this->userRepository->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupports()
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $unsupportedRequest = new Request();
        $unsupportedRequest->headers->set('AuthToken', '');

        self::assertEquals(true, $this->sut->supports($supportedRequest));
        self::assertEquals(false, $this->sut->supports($unsupportedRequest));
    }

    public function testAuthenticate()
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $user = (new User())
            ->setEmail('a@b.com');
        $postAuthToken = new PostAuthenticationToken($user, 'a_firewall', ['ROLE_LAY_DEPUTY']);
        $this->userRepository->findOneBy(['email' => 'a@b.com'])->willReturn($user);

        $this->redisClient->get('AuthTokenValue')->willReturn(serialize($postAuthToken));

        $passport = new SelfValidatingPassport(
            new UserBadge($postAuthToken->getUserIdentifier(), function ($userEmail) {
                $user = $this->userRepository->findOneBy(['email' => strtolower($userEmail)]);

                if ($user instanceof User) {
                    return $user;
                }

                throw new UserNotFoundException('User not found');
            })
        );

        self::assertEquals($passport, $this->sut->authenticate($supportedRequest));
    }

    public function testAuthenticateRedisKeyDoesNotExistThrowsError()
    {
        self::expectExceptionObject(new UserNotFoundException('User not found'));

        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $this->redisClient->get('AuthTokenValue')->willReturn(null);

        $this->sut->authenticate($supportedRequest);
    }

    public function testAuthenticateUserWithIdentifierInTokenDoesNotExistReturnsPassport()
    {
        $supportedRequest = new Request();
        $supportedRequest->headers->set('AuthToken', 'AuthTokenValue');

        $user = (new User())
            ->setEmail('a@b.com');
        $postAuthToken = new PostAuthenticationToken($user, 'a_firewall', ['ROLE_LAY_DEPUTY']);

        $this->redisClient->get('AuthTokenValue')->shouldBeCalled()->willReturn(serialize($postAuthToken));
        $this->userRepository->findOneBy(['email' => 'a@b.com'])->shouldNotBeCalled();

        $passport = new SelfValidatingPassport(
            new UserBadge($postAuthToken->getUserIdentifier(), function ($userEmail) {
                $user = $this->userRepository->findOneBy(['email' => strtolower($userEmail)]);

                if ($user instanceof User) {
                    return $user;
                }

                throw new UserNotFoundException('User not found');
            })
        );

        self::assertEquals($passport, $this->sut->authenticate($supportedRequest));
    }
}
