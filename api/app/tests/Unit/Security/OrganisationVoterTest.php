<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Security;

use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Security\OrganisationVoter;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class OrganisationVoterTest extends KernelTestCase
{
    private User $user;
    private Organisation $subject;
    private Security&Stub $security;
    private OrganisationVoter $sut;

    public function setUp(): void
    {
        $this->user = new User();
        $this->subject = new Organisation();

        $this->security = self::createStub(Security::class);
        $this->security->method('isGranted')->willReturn(false);

        $this->sut = new OrganisationVoter($this->security);
    }

    public function testOrganisationContainsLoggedInUser(): void
    {
        $this->subject->addUser($this->user);

        $token = self::createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);

        $attributes = [$this->sut::VIEW];
        $voteResult = $this->sut->vote($token, $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_GRANTED, $voteResult);
    }

    public function testOrganisationDoesNotContainLoggedInUser(): void
    {
        $token = self::createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);

        $attributes = [$this->sut::VIEW, $this->sut::EDIT];
        $voteResult = $this->sut->vote($token, $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_DENIED, $voteResult);
    }

    public function testUnrecognisedAttribute(): void
    {
        $token = self::createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);

        $attributes = ['some-other-attribute'];
        $voteResult = $this->sut->vote($token, $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_ABSTAIN, $voteResult);
    }

    public function testSubjectIsNotOrganisation(): void
    {
        $subject = new \DateTime();

        $token = self::createMock(TokenInterface::class);

        $attributes = [$this->sut::VIEW, $this->sut::EDIT];
        $voteResult = $this->sut->vote($token, $subject, $attributes);

        self::assertEquals($this->sut::ACCESS_ABSTAIN, $voteResult);
    }
}
