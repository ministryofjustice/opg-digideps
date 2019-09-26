<?php declare(strict_types=1);

namespace Tests\AppBundle\Security;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Security\OrganisationVoter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class OrganisationVoterTest extends TestCase
{
    /**
     * @group acs
     */
    public function testOrganisationContainsLoggedInUser()
    {
        $orgMemberUser = new User();
        $org = new Organisation();
        $org->addUser($orgMemberUser);
        
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);
        
        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());
        
        $attributes = [$sut::VIEW];
        $voteResult = $sut->vote($token->reveal(), $org, $attributes);
        
        self::assertEquals($sut::ACCESS_GRANTED, $voteResult);
    }

    /**
     * @group acs
     */
    public function testOrganisationDoesNotContainsLoggedInUser()
    {
        $user = new User();
        $org = new Organisation();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $org, $attributes);

        self::assertEquals($sut::ACCESS_DENIED, $voteResult);
    }

    /**
     * @group acs
     */
    public function testUnrecognisedAttribute()
    {
        $user = new User();
        $org = new Organisation();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = ['some-other-attribute'];
        $voteResult = $sut->vote($token->reveal(), $org, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }

    /**
     * @group acs
     */
    public function testSubjectIsNotOrganisation()
    {
        $subject = new DateTime();

        $token = self::prophesize(TokenInterface::class);

        $security = self::prophesize(Security::class);
        $sut = new OrganisationVoter($security->reveal());

        $attributes = ['some-other-attribute'];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }
}
