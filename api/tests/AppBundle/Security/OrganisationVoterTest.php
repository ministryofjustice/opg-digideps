<?php declare(strict_types=1);

namespace Tests\App\Security;

use App\Entity\Organisation;
use App\Entity\User;
use App\Security\OrganisationVoter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class OrganisationVoterTest extends TestCase
{
    public function testOrganisationContainsLoggedInUser()
    {
        $orgMemberUser = new User();
        $subject = new Organisation();
        $subject->addUser($orgMemberUser);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = [$sut::VIEW];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_GRANTED, $voteResult);
    }

    public function testOrganisationDoesNotContainsLoggedInUser()
    {
        $user = new User();
        $subject = new Organisation();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_DENIED, $voteResult);
    }

    public function testUnrecognisedAttribute()
    {
        $user = new User();
        $subject = new Organisation();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = ['some-other-attribute'];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }

    public function testSubjectIsNotOrganisation()
    {
        $subject = new DateTime();

        $token = self::prophesize(TokenInterface::class);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }
}
