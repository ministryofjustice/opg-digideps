<?php declare(strict_types=1);

namespace Tests\AppBundle\Security;


use AppBundle\Entity\Client;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Security\ClientVoter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class ClientVoterTest extends TestCase
{
    public function testClientBelongsToActiveOrg()
    {
        $orgMemberUser = new User();
        $org = new Organisation();
        $org->addUser($orgMemberUser);
        $org->setIsActivated(true);
        
        $subject = new Client();
        $subject->setOrganisation($org);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);

        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_GRANTED, $voteResult);
    }

    public function testClientBelongsToInactiveOrgButUserBelongsToClient()
    {
        $orgMemberUser = new User();
        $org = new Organisation();
        $org->addUser($orgMemberUser);
        $org->setIsActivated(false);

        $subject = new Client();
        $subject->setOrganisation($org);
        $subject->setUsers([$orgMemberUser]);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);

        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_GRANTED, $voteResult);
    }

    public function testUserIsAdmin()
    {
        $user = new User();

        $subject = new Client();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $security->isGranted('ROLE_ADMIN')->willReturn(true);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_GRANTED, $voteResult);
    }
    
    public function testClientBelongsToInactiveOrg()
    {
        $orgMemberUser = new User();
        $org = new Organisation();
        $org->addUser($orgMemberUser);
        $org->setIsActivated(false);

        $subject = new Client();
        $subject->setOrganisation($org);

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);

        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_DENIED, $voteResult);
    }

    public function testSubjectIsNotClient()
    {
        $subject = new DateTime();

        $token = self::prophesize(TokenInterface::class);

        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }

    public function testUnrecognisedAttribute()
    {
        $user = new User();
        $subject = new Client();

        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = ['some-other-attribute'];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }
}
