<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Service\CasrecVerificationService;
use AppBundle\Service\UserRegistrationService;
use Doctrine\ORM\EntityManager;
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

        $em = m::mock(EntityManager::class)
//            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->getMock();

        $mockCasrecVerificationService = m::mock(CasrecVerificationService::class);
        $mockCasrecVerificationService->shouldIgnoreMissing();

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);
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

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('flush')->twice()
            ->shouldReceive('persist')->with($mockUser)->once()
            ->shouldReceive('persist')->with($mockClient)->once()
            ->getMock();

        $mockCasrecVerificationService = m::mock('\AppBundle\Service\CasrecVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

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

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('persist')->with($mockUser)->once()->andThrow($exception)
            ->getMock();

        $mockCasrecVerificationService = m::mock('\AppBundle\Service\CasrecVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

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

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('persist')->with($mockUser)->once()
            ->shouldReceive('persist')->with($mockClient)->once()->andThrow($exception)
            ->getMock();

        $mockCasrecVerificationService = m::mock('\AppBundle\Service\CasrecVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
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

        $mockClient = m::mock(Client::class)
            ->shouldIgnoreMissing(true)
            ->makePartial()
            ->shouldReceive('getCourtDate')->andReturn(new \DateTime('2015-05-04'))
            ->getMock();

        $datetime = new \DateTime('2015-05-04');
        $mockClient->shouldIgnoreMissing(true)
            ->shouldReceive('getCourtDate')->andReturn($datetime)
            ->getMock();

        $mockConnection = m::mock('\Doctrine\Common\Persistence\Connection')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')
            ->shouldReceive('commit')
            ->getMock();

        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
//            ->shouldReceive('findOneBy')->with(['email' => 'zac@thetolleys.com'])->andReturn(null)
            ->shouldReceive('findOneByEmail')->with('zac@thetolleys.com')->andReturn(null)
            ->getMock();

        $mockClientRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(false)
            ->shouldReceive('findOneBy')->withAnyArgs()->andReturn(false)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('flush')->twice()
            ->shouldReceive('persist')->with($mockUser)
            ->shouldReceive('persist')->with($mockClient)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\User')->andReturn($mockUserRepository)
            ->shouldReceive('getRepository')->with('AppBundle\Entity\Client')->andReturn($mockClientRepository)
            ->getMock();

        $mockCasrecVerificationService = m::mock('\AppBundle\Service\CasrecVerificationService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isMultiDeputyCase')->with('12341234')->andReturn(false)
            ->shouldReceive('getLastMatchedDeputyNumbers')->andReturn(['123'])
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function tearDown()
    {
        m::close();
    }
}
