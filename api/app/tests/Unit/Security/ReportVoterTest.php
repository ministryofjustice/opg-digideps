<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Domain\Report\ReportAccessService;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Security\ReportVoter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ReportVoterTest extends TestCase
{
    private function makeReportVoter(?bool $admin, int ...$reportIds): ReportVoter
    {
        $security = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();
        if ($admin === null) {
            $security->expects($this->never())->method('isGranted');
        } else {
            $security->expects($this->once())->method('isGranted')->with('ROLE_ADMIN')->willReturn($admin);
        }
        $result = $this->createMock(Result::class);
        $result->method('fetchFirstColumn')->willReturn($reportIds);
        $connection = $this->createMock(Connection::class);
        $connection->method('executeQuery')->willReturn($result);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);
        return new ReportVoter($security, new ReportAccessService($em), $this->createStub(AuthorizationCheckerInterface::class), $this->createStub(LoggerInterface::class));
    }

    private function makeToken(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);
        return $token;
    }

    public function testVoteDenied(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getId')->willReturn(100);
        $report = $this->createStub(Report::class);
        $report->method('getId')->willReturn(5);
        $token = $this->makeToken($user);
        $reportVoter = $this->makeReportVoter(false, 1, 2, 3);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }
    public function testVoteGranted(): void
    {
        $user = $this->createStub(User::class);
        $user->method('getId')->willReturn(100);
        $report = $this->createStub(Report::class);
        $report->method('getId')->willReturn(2);
        $token = $this->makeToken($user);
        $reportVoter = $this->makeReportVoter(false, 1, 2, 3);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteNoLogin(): void
    {
        $reportVoter = $this->makeReportVoter(null);
        $token = $this->makeToken(null);
        $report = $this->createStub(Report::class);
        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteAdmin(): void
    {
        $reportVoter = $this->makeReportVoter(true);
        $token = $this->makeToken($this->createStub(User::class));
        $report = $this->createStub(Report::class);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testSupportsAttribute(): void
    {
        $this->assertTrue(new ReportVoter($this->createStub(Security::class), $this->createStub(ReportAccessService::class), $this->createStub(AuthorizationCheckerInterface::class), $this->createStub(LoggerInterface::class))->supportsAttribute(ReportVoter::ACCESS));
        $this->assertFalse(new ReportVoter($this->createStub(Security::class), $this->createStub(ReportAccessService::class), $this->createStub(AuthorizationCheckerInterface::class), $this->createStub(LoggerInterface::class))->supportsAttribute('DELETE'));
    }

    public function testSupportsType(): void
    {
        $this->assertTrue(new ReportVoter($this->createStub(Security::class), $this->createStub(ReportAccessService::class), $this->createStub(AuthorizationCheckerInterface::class), $this->createStub(LoggerInterface::class))->supportsType(Report::class));
        $this->assertFalse(new ReportVoter($this->createStub(Security::class), $this->createStub(ReportAccessService::class), $this->createStub(AuthorizationCheckerInterface::class), $this->createStub(LoggerInterface::class))->supportsType(Client::class));
    }
}
