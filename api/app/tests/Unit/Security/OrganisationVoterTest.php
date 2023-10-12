<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Organisation;
use App\Entity\User;
use App\Security\OrganisationVoter;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class OrganisationVoterTest extends KernelTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->user = new User();
        $this->subject = new Organisation();

        $this->security = self::prophesize(Security::class);
        $this->security->isGranted('ROLE_ADMIN')->willReturn(false);

        $this->sut = new OrganisationVoter($this->security->reveal());
    }

    public function testOrganisationContainsLoggedInUser()
    {
        $this->subject->addUser($this->user);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);

        $attributes = [$this->sut::VIEW];
        $voteResult = $this->sut->vote($token->reveal(), $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_GRANTED, $voteResult);
    }

    public function testOrganisationDoesNotContainsLoggedInUser()
    {
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);

        $attributes = [$this->sut::VIEW, $this->sut::EDIT];
        $voteResult = $this->sut->vote($token->reveal(), $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_DENIED, $voteResult);
    }

    public function testUnrecognisedAttribute()
    {
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($this->user);

        $attributes = ['some-other-attribute'];
        $voteResult = $this->sut->vote($token->reveal(), $this->subject, $attributes);

        self::assertEquals($this->sut::ACCESS_ABSTAIN, $voteResult);
    }

    public function testSubjectIsNotOrganisation()
    {
        $subject = new \DateTime();

        $token = self::prophesize(TokenInterface::class);

        $attributes = [$this->sut::VIEW, $this->sut::EDIT];
        $voteResult = $this->sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($this->sut::ACCESS_ABSTAIN, $voteResult);
    }
}
