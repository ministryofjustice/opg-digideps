<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Service\UserRegistrationService;
use Doctrine\ORM\ORMInvalidArgumentException;
use Mockery as m;

class UserRegistrationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserRegistrationService
     */
    private $userRegistrationService;

    public function setup()
    {
        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->getMock();

        $this->casRec = m::mock('\AppBundle\Entity\CasRec');

        $mockCasRecRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(false)
            ->shouldReceive('findOneBy')->withAnyArgs()->andReturn($this->casRec)
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
//            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\CasRec')->andReturn($mockCasRecRepository)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function populateUser()
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $user = new User();
        $user->recreateRegistrationToken();
        $this->userRegistrationService->populateUser($user, $data);

        $this->assertEquals(User::ROLE_LAY_DEPUTY, $user->getRoleName());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('zac@thetolleys.com', $user->getEmail());
        $this->assertFalse($user->getActive());
        $this->assertNotEmpty($user->getRegistrationToken());
        $this->assertNotNull($user->getTokenDate());

        $token_time = $user->getTokenDate();
        $now = new \DateTime();
        $diffInSeconds = $now->getTimestamp() - $token_time->getTimestamp();

        $this->assertLessThan(60, $diffInSeconds);  // time was set to just now
    }

    /**
     * @test
     */
    public function populateClient()
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $client = new Client();
        $this->userRegistrationService->populateClient($client, $data);

        $this->assertEquals('Cross-Tolley', $client->getLastname());
        $this->assertEquals('12341234', $client->getCaseNumber());
    }

    /**
     * @test
     */
    public function saveUserAndClientAndJoinThem()
    {
        $mockUser = m::mock('\AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('addUser')->once()->with($mockUser)
            ->getMock();

        $mockConnection = m::mock('\Doctrine\Common\Persistence\Connection')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')->once()
            ->shouldReceive('commit')->once()
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('flush')->twice()
            ->shouldReceive('persist')->with($mockUser)->once()
            ->shouldReceive('persist')->with($mockClient)->once()
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     * @expectedException \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function rollbackWhenSavingUserWithError()
    {
        $mockUser = m::mock('\AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('addUser')->with($mockUser)
            ->getMock();

        $mockConnection = m::mock('\Doctrine\Common\Persistence\Connection')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')->once()
            ->shouldReceive('rollback')->once()
            ->getMock();

        $exception = ORMInvalidArgumentException::invalidObject('EntityManager#persist()', $mockUser);

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('persist')->with($mockUser)->once()->andThrow($exception)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     * @expectedException \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function rollbackWhenSavingClientWithError()
    {
        $mockUser = m::mock('\AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('addUser')->with($mockUser)
            ->getMock();

        $mockConnection = m::mock('\Doctrine\Common\Persistence\Connection')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')->once()
            ->shouldReceive('rollback')->once()
            ->getMock();

        $exception = ORMInvalidArgumentException::invalidObject('EntityManager#persist()', $mockUser);

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('persist')->with($mockUser)->once()
            ->shouldReceive('persist')->with($mockClient)->once()->andThrow($exception)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     */
    public function userIsNotUnique()
    {
        $user = new User();
        $user->setFirstname('zac');
        $user->setLastname('tolley');
        $user->setEmail('zac@thetolleys.com');

        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findOneBy')->with(['email' => 'zac@thetolleys.com'])->andReturn($user)
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $result = $this->userRegistrationService->userIsUnique($user);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function userIsUnique()
    {
        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findOneBy')->with(['email' => 'zaz@thetolleys.com'])->andReturn(null)
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $user2 = new User();
        $user2->setFirstname('zac');
        $user2->setLastname('tolley');
        $user2->setEmail('zaz@thetolleys.com');

        $result = $this->userRegistrationService->userIsUnique($user2);

        $this->assertTrue($result);
    }

    /** @test */
    public function renderRegistrationHtmlEmail()
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $mockUser = m::mock('\AppBundle\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('addUser')->with($mockUser)
            ->getMock();

        $mockConnection = m::mock('\Doctrine\Common\Persistence\Connection')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')
            ->shouldReceive('commit')
            ->getMock();

        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findOneBy')->with(['email' => 'zac@thetolleys.com'])->andReturn(null)
            ->getMock();

        $this->casRec = m::mock('\AppBundle\Entity\CasRec')
            ->shouldReceive('getDeputyPostCode')->andReturn(null)
            ->shouldReceive('getDeputyNo')->andReturn('D01')
            ->getMock();

        $mockCasRecRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(false)
            ->shouldReceive('findOneBy')->withAnyArgs()->andReturn($this->casRec)
            ->getMock();

        $mockClientRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(false)
            ->shouldReceive('findOneBy')->withAnyArgs()->andReturn(false)
            ->getMock();

        $em = m::mock('\Doctrine\Common\Persistence\ObjectManager')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('flush')->twice()
            ->shouldReceive('persist')->with($mockUser)
            ->shouldReceive('persist')->with($mockClient)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\CasRec')->andReturn($mockCasRecRepository)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\Client')->andReturn($mockClientRepository)
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em);

        $this->userRegistrationService->selfRegisterUser($data);
    }
}
