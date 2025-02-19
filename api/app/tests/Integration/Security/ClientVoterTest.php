<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use App\Security\ClientVoter;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\UserTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;

class ClientVoterTest extends KernelTestCase
{
    use ProphecyTrait;

    /** @var ClientVoter */
    private $voter;

    /** @var Security|MockObject */
    private $security;

    /** @var TokenInterface|MockObject */
    private $token;

    /** @var User */
    private $user;

    /** @var int */
    private $decision;

    public function setUp(): void
    {
        $this->user = new User();
        $this->token = $this->createMock(TokenInterface::class);
        $this->security = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();
        $this->voter = new ClientVoter($this->security);
    }

    /**
     * @test
     */
    public function deniesAccessToUnauthenticaatedUsers()
    {
        $this
            ->ensureUserIsNotLoggedIn()
            ->castVoteAgainstClient(new Client())
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    private function assertDecisionIs(int $expectedDecision): void
    {
        $this->assertEquals($expectedDecision, $this->decision);
    }

    private function castVoteAgainstClient(Client $client): ClientVoterTest
    {
        $this->decision = $this->voter->vote($this->token, $client, [ClientVoter::VIEW, ClientVoter::EDIT]);

        return $this;
    }

    private function ensureUserIsNotLoggedIn(): ClientVoterTest
    {
        $this->token->method('getUser')->willReturn(null);

        return $this;
    }

    /**
     * @test
     */
    public function grantsAccessToAdminUsers()
    {
        $this
            ->ensureUserIsLoggedInWithRole('ROLE_ADMIN')
            ->castVoteAgainstClient(new Client())
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    private function ensureUserIsLoggedInWithRole(string $role): ClientVoterTest
    {
        $this->token->method('getUser')->willReturn($this->user);

        if ('ROLE_ADMIN' === $role) {
            // The ROLE_ADMIN check verifies the users role with the isGranted($roleName) method.
            $this->security->method('isGranted')->with($role)->willReturn(true);
        } else {
            $this->user->setRoleName($role);
        }

        return $this;
    }

    /**
     * @test
     */
    public function grantsAccessToLayUsersIfClientBelongsToThem()
    {
        $client = new Client();

        $this
            ->ensureUserIsLoggedInWithRole('ROLE_LAY_DEPUTY')
            ->ensureClientBelongsToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    private function ensureClientBelongsToUser(Client $client): ClientVoterTest
    {
        $client->addUser($this->user);

        return $this;
    }

    /**
     * @test
     */
    public function deniesAccessToLayUsersIfClientDoesNotBelongToThem()
    {
        $client = new Client();

        $this
            ->ensureUserIsLoggedInWithRole('ROLE_LAY_DEPUTY')
            ->ensureClientDoesNotBelongToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    //    todo-aie add test post DDPB-3051
    //    /**
    //     * @test
    //     */
    //    public function denies_access_to_non_lay_users_if_client_belongs_to_users_inactive_organisation_despite_client_belonging_to_user()
    //    {
    //        $client = new Client();
    //        $organisation = new Organisation();
    //        $organisation->setIsActivated(false);
    //
    //        $this
    //            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
    //            ->ensureClientAndUserBelongToSameOrganisation($client, $organisation)
    //            ->ensureClientBelongsToUser($client)
    //            ->castVoteAgainstClient($client)
    //            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    //    }

    private function ensureClientDoesNotBelongToUser(Client $client): ClientVoterTest
    {
        $client->removeUser($this->user);

        return $this;
    }

    /**
     * @test
     */
    public function grantsAccessToNonLayUsersIfClientBelongsToUsersActivatedOrganisation()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(true);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToSameOrganisation($client, $organisation)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    private function ensureClientAndUserBelongToSameOrganisation(Client $client, Organisation $organisation): ClientVoterTest
    {
        $organisation->addUser($this->user);
        $client->setOrganisation($organisation);

        return $this;
    }

    /**
     * @test
     */
    public function deniesAccessToNonLayUsersIfClientBelongsToADifferentActivatedOrganisation()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(true);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToDifferentOrganisations($client, $organisation)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    private function ensureClientAndUserBelongToDifferentOrganisations(Client $client, Organisation $organisation): ClientVoterTest
    {
        $usersOrganisation = (new Organisation())->setIsActivated(true);
        $usersOrganisation->addUser($this->user);
        $client->setOrganisation($organisation);

        return $this;
    }

    /**
     * @test
     */
    public function deniesAccessToNonLayUsersIfClientBelongsToUsersInactiveOrganisationButDoesNotBelongToUser()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(false);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToSameOrganisation($client, $organisation)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    /**
     * @test
     */
    public function deniesAccessToNonLayUsersIfClientBelongsActiveOrganisationAndTheUserDespiteUserNotBeingInTheOrganisation()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(true);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToDifferentOrganisations($client, $organisation)
            ->ensureClientBelongsToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    /**
     * @test
     */
    public function allowsAccessToNonLayUsersIfClientBelongsToInactiveOrganisationAndTheUserDespiteUserNotBeingInTheOrganisation()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(false);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToDifferentOrganisations($client, $organisation)
            ->ensureClientBelongsToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    /**
     * @dataProvider deleteClientProvider
     *
     * @test
     */
    public function determineDeletePermission(User $user, Client $client, int $expectedPermission)
    {
        $security = self::prophesize(Security::class);

        /** @var ClientVoter() $sut */
        $sut = new ClientVoter($security->reveal());

        $token = new UsernamePasswordToken($user, 'credentials', 'memory');

        self::assertEquals($expectedPermission, $sut->vote($token, $client, [ClientVoter::DELETE]));
    }

    public function deleteClientProvider()
    {
        $userTestHelper = new UserTestHelper();
        $clientTestHelp = new ClientTestHelper();

        $kernel = self::bootKernel();
        $em = static::getContainer()->get('em');

        $client = $clientTestHelp->generateClient($em);
        $em->persist($client);
        $em->flush();

        $lay = $userTestHelper->createAndPersistUser($em, $client, User::ROLE_LAY_DEPUTY);

        $admin = $userTestHelper->createAndPersistUser($em, null, User::ROLE_ADMIN);
        $superAdmin = $userTestHelper->createAndPersistUser($em, null, User::ROLE_SUPER_ADMIN);
        $adminManager = $userTestHelper->createAndPersistUser($em, null, User::ROLE_ADMIN_MANAGER);

        $pa = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PA);
        $paNamed = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PA_NAMED);
        $paAdmin = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PA_ADMIN);
        $paTeamMember = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PA_TEAM_MEMBER);

        $prof = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PROF);
        $profNamed = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PROF_NAMED);
        $profAdmin = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PROF_ADMIN);
        $profTeamMember = $userTestHelper->createAndPersistUser($em, null, User::ROLE_PROF_TEAM_MEMBER);

        return [
            'Lay Deputy deletes Client' => [$lay, $client, -1],
            'PA Deputy deletes Client' => [$pa, $client, -1],
            'PA Team Member deletes Client' => [$paTeamMember, $client, -1],
            'PA Named Deputy deletes Client' => [$paNamed, $client, -1],
            'PA Admin Deputy deletes Client' => [$paAdmin, $client, -1],
            'Prof Deputy deletes Client' => [$prof, $client, -1],
            'Prof Team Member deletes Client' => [$profTeamMember, $client, -1],
            'Prof Named Deputy deletes Client' => [$profNamed, $client, -1],
            'Prof Admin Deputy deletes Client' => [$profAdmin, $client, -1],
            'Admin deletes Client' => [$admin, $client, -1],
            'Admin Manager deletes Client' => [$adminManager, $client, 1],
            'Super Admin deletes Client' => [$superAdmin, $client, 1],
        ];
    }
}
