<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Repository\UserRepository;
use App\Security\LoginRequestAuthenticator;
use App\Service\Auth\AuthService;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginRequestAuthenticatorTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function supports()
    {
        $userRepo = self::prophesize(UserRepository::class);
        $attemptsInTimeChecker = self::prophesize(AttemptsInTimeChecker::class);
        $incrementalWaitingTimechecker = self::prophesize(AttemptsIncrementalWaitingChecker::class);
        $authService = self::prophesize(AuthService::class);
        $tokenStorage = self::prophesize(TokenStorageInterface::class);
        $logger = self::prophesize(LoggerInterface::class);

        $sut = new LoginRequestAuthenticator(
            $userRepo->reveal(),
            $attemptsInTimeChecker->reveal(),
            $incrementalWaitingTimechecker->reveal(),
            $authService->reveal(),
            $tokenStorage->reveal(),
            $logger->reveal()
        );

        $supportedRequest = new Request();
        $supportedRequest->request->;

        self::assertEquals(true, $sut->supports());
    }
}
