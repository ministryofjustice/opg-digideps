<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Security\ReportVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class ReportVoterTest extends TestCase
{
    private const int UID1 = 123456789;
    private const int UID2 = 987654321;
    private const int UID3 = 147258369;

    private function makeReportVoter(?bool $admin): ReportVoter
    {
        $security = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();
        if ($admin === null) {
            $security->expects($this->never())->method('isGranted');
        } else {
            $security->expects($this->once())->method('isGranted')->with('ROLE_ADMIN')->willReturn($admin);
        }
        return new ReportVoter($security);
    }

    private function makeToken(?User $user): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);
        return $token;
    }

    private function makeUser(bool $lay, ?int $deputyUid = null, ?Deputy $deputy = null): User
    {
        $user = new User();
        $user->setDeputyUid($deputyUid);
        $user->setDeputy($deputy);
        $user->setRoleName($lay ? User::ROLE_LAY_DEPUTY : User::ROLE_ORG_NAMED);
        return $user;
    }

    private function makeDeputy(int $deputyUid, ?Organisation $organisation = null): Deputy
    {
        $deputy = $this->getMockBuilder(Deputy::class)->disableOriginalConstructor()->getMock();
        $deputy->method('getDeputyUid')->willReturn("{$deputyUid}");
        $deputy->method('getOrganisation')->willReturn($organisation);
        return $deputy;
    }

    public function makeCourtOrder(Deputy ...$deputies): CourtOrder
    {
        $courtOrder = $this->getMockBuilder(CourtOrder::class)->disableOriginalConstructor()->getMock();
        $courtOrder->method('getActiveDeputies')->willReturn([...$deputies]);
        return $courtOrder;
    }

    private function makeReport(bool $hybrid, Deputy ...$deputies): Report
    {
        $courtOrders = [$this->makeCourtOrder(...$deputies)];
        if ($hybrid) {
            $courtOrders[] = $this->makeCourtOrder(...$deputies);
        }
        $report = $this->getMockBuilder(Report::class)->disableOriginalConstructor()->getMock();
        $report->expects($this->once())->method('getActiveCourtOrders')->willReturn($courtOrders);
        return $report;
    }

    public function makeOrganisation(User ...$users): Organisation
    {
        $organisation = new Organisation();
        $organisation->setId(123);
        $organisation->setIsActivated(true);
        foreach ($users as $user) {
            $organisation->addUser($user);
            $user->addOrganisation($organisation);
        }
        return $organisation;
    }

    public function testVoteNotLayGranted(): void
    {
        $user = $this->makeUser(false, self::UID1);
        $organisation = $this->makeOrganisation($user);
        $token = $this->makeToken($user);
        $report = $this->makeReport(false, $this->makeDeputy(self::UID2, $organisation));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteNotLayDenied(): void
    {
        $user = $this->makeUser(false, self::UID1);
        $organisation = $this->makeOrganisation($this->makeUser(false, self::UID3));
        $token = $this->makeToken($user);
        $report = $this->makeReport(false, $this->makeDeputy(self::UID2, $organisation));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }
    public function testVoteLayGranted(): void
    {
        $token = $this->makeToken($this->makeUser(true, self::UID1));
        $report = $this->makeReport(false, $this->makeDeputy(self::UID1));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteLayGrantedHybrid(): void
    {
        $token = $this->makeToken($this->makeUser(true, self::UID1));
        $report = $this->makeReport(true, $this->makeDeputy(self::UID1));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteLayDeniedHybrid(): void
    {
        $token = $this->makeToken($this->makeUser(true, self::UID1));
        $report = $this->makeReport(true, $this->makeDeputy(self::UID2));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteLayGrantedFallback(): void
    {
        $deputy = $this->makeDeputy(self::UID1);
        $token = $this->makeToken($this->makeUser(true, deputy: $deputy));
        $report = $this->makeReport(false, $deputy);
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteLayDenied(): void
    {
        $token = $this->makeToken($this->makeUser(true, self::UID1));
        $report = $this->makeReport(false, $this->makeDeputy(self::UID2));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
    }

    public function testVoteLayDeniedFallback(): void
    {
        $token = $this->makeToken($this->makeUser(true, deputy: $this->makeDeputy(self::UID1)));
        $report = $this->makeReport(false, $this->makeDeputy(self::UID2));
        $reportVoter = $this->makeReportVoter(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $reportVoter->vote($token, $report, [ReportVoter::ACCESS]));
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
        $this->assertTrue(new ReportVoter($this->createStub(Security::class))->supportsAttribute(ReportVoter::ACCESS));
        $this->assertFalse(new ReportVoter($this->createStub(Security::class))->supportsAttribute('DELETE'));
    }

    public function testSupportsType(): void
    {
        $this->assertTrue(new ReportVoter($this->createStub(Security::class))->supportsType(Report::class));
        $this->assertFalse(new ReportVoter($this->createStub(Security::class))->supportsType(Client::class));
    }
}
