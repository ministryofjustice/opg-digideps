<?php declare(strict_types=1);

namespace Tests\AppBundle\Security;

use AppBundle\Entity\Client;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Security\ClientVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class ClientVoterTest extends TestCase
{
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
    public function denies_access_to_unauthenticaated_users()
    {
        $this
            ->ensureUserIsNotLoggedIn()
            ->castVoteAgainstClient(new Client())
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    /**
     * @test
     */
    public function grants_access_to_admin_users()
    {
        $this
            ->ensureUserIsLoggedInWithRole('ROLE_ADMIN')
            ->castVoteAgainstClient(new Client())
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    /**
     * @test
     */
    public function grants_access_to_lay_users_if_client_belongs_to_them()
    {
        $client = new Client();

        $this
            ->ensureUserIsLoggedInWithRole('ROLE_LAY_DEPUTY')
            ->ensureClientBelongsToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_GRANTED);
    }

    /**
     * @test
     */
    public function denies_access_to_lay_users_if_client_does_not_belong_to_them()
    {
        $client = new Client();

        $this
            ->ensureUserIsLoggedInWithRole('ROLE_LAY_DEPUTY')
            ->ensureClientDoesNotBelongToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    /**
     * @test
     */
    public function grants_access_to_non_lay_users_if_client_belongs_to_users_activated_organisation()
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

    /**
     * @test
     */
    public function denies_access_to_non_lay_users_if_client_belongs_to_a_different_activated_organisation()
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

    /**
     * @test
     */
    public function denies_access_to_non_lay_users_if_client_belongs_to_users_inactive_organisation()
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
    public function denies_access_to_non_lay_users_if_client_belongs_to_users_inactivate_organisation_despite_client_belonging_to_user()
    {
        $client = new Client();
        $organisation = new Organisation();
        $organisation->setIsActivated(false);

        $this
            ->ensureUserIsLoggedInWithRole('NOT_LAY_DEPUTY')
            ->ensureClientAndUserBelongToSameOrganisation($client, $organisation)
            ->ensureClientBelongsToUser($client)
            ->castVoteAgainstClient($client)
            ->assertDecisionIs(ClientVoter::ACCESS_DENIED);
    }

    /**
     * @param string $role
     * @return ClientVoterTest
     */
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
     * @return ClientVoterTest
     */
    private function ensureUserIsNotLoggedIn(): ClientVoterTest
    {
        $this->token->method('getUser')->willReturn(null);

        return $this;
    }

    /**
     * @param Client $client
     * @return ClientVoterTest
     */
    private function ensureClientBelongsToUser(Client $client): ClientVoterTest
    {
        $client->addUser($this->user);

        return $this;
    }

    /**
     * @param Client $client
     * @return ClientVoterTest
     */
    private function ensureClientDoesNotBelongToUser(Client $client): ClientVoterTest
    {
        $client->removeUser($this->user);

        return $this;
    }


    /**
     * @param Client $client
     * @param Organisation $organisation
     * @return ClientVoterTest
     */
    private function ensureClientAndUserBelongToSameOrganisation(Client $client, Organisation $organisation): ClientVoterTest
    {
        $organisation->addUser($this->user);
        $client->setOrganisation($organisation);

        return $this;
    }

    /**
     * @param Client $client
     * @param Organisation $organisation
     * @return ClientVoterTest
     */
    private function ensureClientAndUserBelongToDifferentOrganisations(Client $client, Organisation $organisation): ClientVoterTest
    {
        $organisation->addUser($this->user);
        $client->setOrganisation((new Organisation())->setIsActivated(true));

        return $this;
    }

    /**
     * @param Client $client
     * @return ClientVoterTest
     */
    private function castVoteAgainstClient(Client $client): ClientVoterTest
    {
        $this->decision = $this->voter->vote($this->token, $client, [ClientVoter::VIEW, ClientVoter::EDIT]);

        return $this;
    }

    /**
     * @param int $expectedDecision
     */
    private function assertDecisionIs(int $expectedDecision): void
    {
        $this->assertEquals($expectedDecision, $this->decision);
    }
}
