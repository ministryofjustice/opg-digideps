<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mockery\MockInterface;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

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
        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new DateTime('2014-06-06'));
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
}
