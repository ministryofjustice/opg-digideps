<?php declare(strict_types=1);

namespace Tests\AppBundle\Security;


use AppBundle\Entity\Client;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\OrganisationInterface;
use AppBundle\Entity\User;
use AppBundle\Security\ClientVoter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Mockery as m;

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

         /** @var TokenInterface|ObjectProphecy $token */
         $token = self::prophesize(TokenInterface::class);
         $token->getUser()->willReturn($orgMemberUser);

         /** @var Security|ObjectProphecy $security */
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

        /** @var TokenInterface|ObjectProphecy $token */
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        /** @var Security|ObjectProphecy $security */
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

        /** @var TokenInterface|ObjectProphecy $token */
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($orgMemberUser);

        /** @var Security|ObjectProphecy $security */
        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_DENIED, $voteResult);
    }

    public function testSubjectIsNotClient()
    {
        $subject = new DateTime();

        /** @var TokenInterface|ObjectProphecy $token */
        $token = self::prophesize(TokenInterface::class);

        /** @var Security|ObjectProphecy $security */
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

        /** @var TokenInterface|ObjectProphecy $token */
        $token = self::prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        /** @var Security|ObjectProphecy $security */
        $security = self::prophesize(Security::class);
        $sut = new ClientVoter($security->reveal());

        $attributes = ['some-other-attribute'];
        $voteResult = $sut->vote($token->reveal(), $subject, $attributes);

        self::assertEquals($sut::ACCESS_ABSTAIN, $voteResult);
    }

    /**
     * @return array
     */
    public function getDeputyClienttVariations(): array
    {
        return [
            [
                'deputyBelongsToOrg' =>  false, 'clientBelongsToOrg' => false, 'orgIsActive' => false, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  false, 'clientBelongsToOrg' => false, 'orgIsActive' => true, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  false, 'clientBelongsToOrg' => true, 'orgIsActive' => false, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  false, 'clientBelongsToOrg' => true, 'orgIsActive' => true, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  true, 'clientBelongsToOrg' => false, 'orgIsActive' => false, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  true, 'clientBelongsToOrg' => false, 'orgIsActive' => true, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  true, 'clientBelongsToOrg' => true, 'orgIsActive' => false, 'expected' => VoterInterface::ACCESS_DENIED,
            ],
            [
                'deputyBelongsToOrg' =>  true, 'clientBelongsToOrg' => true, 'orgIsActive' => true, 'expected' => VoterInterface::ACCESS_GRANTED,
            ]
        ];
    }

    /**
     * @dataProvider getDeputyClienttVariations
     */
    public function testVoterGrantsPermission(
        $deputyBelongsToOrg,
        $clientBelongsToOrg,
        $orgIsActive,
        $expectedPermission
    ) {
        $loggedInUser = m::mock(User::class);
        $loggedInUser->shouldReceive('getId')->andReturn(33);

        $org = m::mock(OrganisationInterface::class)->makePartial();
        $org->shouldReceive('isActivated')->andReturn($orgIsActive);

        $token = m::mock(TokenInterface::class)->makePartial();
        $token->shouldReceive('getUser')->andReturn($loggedInUser);

        $subject = m::mock(Client::class)->makePartial();

        if ($deputyBelongsToOrg) {
            $org->shouldReceive('containsUser')->with($loggedInUser)->andReturnTrue();
        } else {
            $org->shouldReceive('containsUser')->with($loggedInUser)->andReturnFalse();
        }

        if ($clientBelongsToOrg) {
            $subject->shouldReceive('getOrganisation')->zeroOrMoreTimes()->andReturn($org);
        } else {
            $subject->shouldReceive('getOrganisation')->zeroOrMoreTimes()->andReturnNull();
        }

        $security = m::mock(Security::class);
        $security->shouldReceive('isGranted')->with('ROLE_ADMIN')->andReturnFalse();

        $sut = new ClientVoter($security);

        $attributes = [$sut::VIEW, $sut::EDIT];
        $voteResult = $sut->vote($token, $subject, $attributes);

        self::assertEquals($expectedPermission, $voteResult);
    }
}
