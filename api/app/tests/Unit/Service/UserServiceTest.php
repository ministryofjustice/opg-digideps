<?php

namespace App\Tests\Unit\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\v2\DTO\InviteeDto;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mockery\MockInterface;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\isInstanceOf;

class UserServiceTest extends TestCase
{
    private User $user;
    private EntityManager&MockInterface $em;
    private ClientRepository&MockInterface $clientRepository;
    private UserRepository&MockInterface $userRepository;
    private UserService $sut;

    public function setUp(): void
    {
        $this->user = new User();
        $this->user->setRoleName(User::ROLE_LAY_DEPUTY);

        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));

        $email = 'test@tester.co.uk';

        $this->em = m::mock(EntityManager::class);
        $this->clientRepository = m::mock(ClientRepository::class);
        $this->userRepository = m::mock(UserRepository::class);

        $this->em->shouldReceive('getRepository')->andReturnUsing(function ($arg) use ($email) {
            switch ($arg) {
                case User::class:
                    return m::mock(EntityRepository::class)->shouldReceive('findOneBy')
                        ->with(['email' => $email])
                        ->andReturn(null)
                        ->getMock();
            }
        });

        $this->sut = new UserService($this->em, $this->clientRepository, $this->userRepository);
    }

    /**
     * Provides logged-in user role and expected registration route that should be set.
     *
     * @return array
     */
    public static function setRoleForLoggedInUser()
    {
        return [
            [User::ROLE_LAY_DEPUTY, User::CO_DEPUTY_INVITE, 100],
            [User::ROLE_ADMIN, User::ADMIN_INVITE, null],
            [User::ROLE_PROF_ADMIN, User::ORG_ADMIN_INVITE, null],
        ];
    }

    /**
     * @dataProvider setRoleForLoggedInUser
     */
    public function testRegistrationRoute($role, $expectedRoute, $clientId)
    {
        $loggedInUser = $this->user;
        $loggedInUser->setRoleName($role);

        $userToAdd = new User();

        $userToAdd->setEmail('test@tester.co.uk');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');
        $this->userRepository->shouldReceive('findOneBy')
            ->with(['email' => 'test@tester.co.uk']);
        $this->clientRepository->shouldReceive('saveUserToClient')->with($userToAdd, $clientId);

        $this->sut->addUser($loggedInUser, $userToAdd, $clientId);

        $this->assertEquals($expectedRoute, $userToAdd->getRegistrationRoute());
    }

    // get or add a user when an existing user with the same email address already exists
    public function testGetOrAddUserExistingUser(): void
    {
        $clientId = 99919992;

        $invitee = new InviteeDto('foo@bar.com', 'Karban', 'Steelcore');

        $existingUser = m::mock(User::class);
        $existingUser->shouldReceive('getRegistrationToken')->andReturn(null);
        $existingUser->shouldReceive('recreateRegistrationToken');
        $existingUser->shouldReceive('setActive')->with(true);

        $this->userRepository->shouldReceive('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->andReturn($existingUser);

        $this->clientRepository->shouldReceive('saveUserToClient')
            ->with($existingUser, $clientId);

        $this->em->shouldReceive('persist')->with($existingUser);
        $this->em->shouldReceive('flush');

        $user = $this->sut->getOrAddUser($invitee, $this->user, 12345667, $clientId);

        self::assertEquals($existingUser, $user);
    }

    // get or add a user when an existing user with the same deputy UID (but different email) exists
    public function testGetOrAddUserPrimaryExists(): void
    {
        $deputyUid = 9988553311;
        $clientId = 1837478367;
        $invitee = new InviteeDto('foo@bar.com', 'Karban', 'Steelcore');

        $existingUser = m::mock(User::class);

        $this->userRepository->shouldReceive('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->andReturn(null);

        $this->userRepository->shouldReceive('findOneBy')
            ->with([
                'deputyUid' => $deputyUid,
                'active' => true,
                'isPrimary' => true,
            ])
            ->andReturn($existingUser);

        $this->em->shouldReceive('persist')->with(isInstanceOf(User::class));
        $this->em->shouldReceive('flush');

        $this->clientRepository->shouldReceive('saveUserToClient')
            ->with(isInstanceOf(User::class), $clientId);

        $user = $this->sut->getOrAddUser($invitee, $this->user, $deputyUid, $clientId);

        assertInstanceOf(User::class, $user);
    }
}
