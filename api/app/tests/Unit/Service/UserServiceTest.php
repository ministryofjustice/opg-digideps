<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private static $deputy1;
    private static $codeputy1;

    public function setUp(): void
    {
        $this->user = new User();
        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));
        $email = 'test@tester.co.uk';

        $this->em = m::mock(EntityManager::class);
        $this->orgService = m::mock(OrgService::class);

        $this->em->shouldReceive('getRepository')->andReturnUsing(function ($arg) use ($email) {
            switch ($arg) {
                case User::class:
                    return m::mock(EntityRepository::class)->shouldReceive('findOneBy')
                        ->with(['email' => $email])
                        ->andReturn(null)
                        ->getMock();
            }
        });

        $this->sut = new UserService($this->em, $this->orgService);
    }

    public function testCoDeputyRegistrationRoute()
    {
        $loggedInUser = $this->user;
        $loggedInUser->setRoleName(User::ROLE_LAY_DEPUTY);

        $userToAdd = new User();

        $userToAdd->setEmail('test@tester.co.uk');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');
        $this->orgService->shouldReceive('addUserToUsersClients')
        ->with($loggedInUser, $userToAdd);

        $this->sut->addUser($loggedInUser, $userToAdd, '');

        $this->assertEquals(User::CO_DEPUTY_INVITE, $userToAdd->getRegistrationRoute());
    }

    public function testAdminRegistrationRoute()
    {
        $loggedInUser = $this->user;
        $loggedInUser->setRoleName(User::ROLE_ADMIN);

        $userToAdd = new User();

        $userToAdd->setEmail('test@tester.co.uk');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');
        $this->orgService->shouldReceive('addUserToUsersClients')
            ->with($loggedInUser, $userToAdd);

        $this->sut->addUser($loggedInUser, $userToAdd, '');

        $this->assertEquals(User::ADMIN_INVITE, $userToAdd->getRegistrationRoute());
    }

    public function testOrgAdminRegistrationRoute()
    {
        $loggedInUser = $this->user;
        $loggedInUser->setRoleName(User::ROLE_PROF_ADMIN);

        $userToAdd = new User();

        $userToAdd->setEmail('test@tester.co.uk');
        $this->em->shouldReceive('persist');
        $this->em->shouldReceive('flush');
        $this->orgService->shouldReceive('addUserToUsersClients')
            ->with($loggedInUser, $userToAdd);

        $this->sut->addUser($loggedInUser, $userToAdd, '');

        $this->assertEquals(User::ORG_ADMIN_INVITE, $userToAdd->getRegistrationRoute());
    }
}
