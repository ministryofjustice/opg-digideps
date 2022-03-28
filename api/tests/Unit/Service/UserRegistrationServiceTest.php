<?php

namespace App\Tests\Unit\Service;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use App\Model\SelfRegisterData;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\PreRegistrationVerificationService;
use App\Service\UserRegistrationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserRegistrationServiceTest extends TestCase
{
    /**
     * @var UserRegistrationService
     */
    private $userRegistrationService;

    public function setUp(): void
    {
        $mockUserRepository = m::mock('\Doctrine\ORM\EntityRepository')
            ->shouldIgnoreMissing(true)
            ->getMock();

        $em = m::mock(EntityManager::class)
//            ->shouldIgnoreMissing(true)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($mockUserRepository)
            ->getMock();

        $mockCasrecVerificationService = m::mock(PreRegistrationVerificationService::class);
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
     * @doesNotPerformAssertions
     */
    public function saveUserAndClientAndJoinThem()
    {
        $mockUser = m::mock('\App\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\App\Entity\Client')
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

        $mockCasrecVerificationService = m::mock('\App\Service\PreRegistrationVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     */
    public function rollbackWhenSavingUserWithError()
    {
        $mockUser = m::mock('\App\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\App\Entity\Client')
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

        $mockCasrecVerificationService = m::mock('\App\Service\PreRegistrationVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->expectException(\Doctrine\ORM\ORMInvalidArgumentException::class);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     */
    public function rollbackWhenSavingClientWithError()
    {
        $mockUser = m::mock('\App\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockClient = m::mock('\App\Entity\Client')
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

        $mockCasrecVerificationService = m::mock('\App\Service\PreRegistrationVerificationService');
        $mockCasrecVerificationService->shouldIgnoreMissing(true);

        $this->expectException(\Doctrine\ORM\ORMInvalidArgumentException::class);

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);

        $this->userRegistrationService->saveUserAndClient($mockUser, $mockClient);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function renderRegistrationHtmlEmail()
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $mockUser = m::mock('\App\Entity\User')
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
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($mockUserRepository)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($mockClientRepository)
            ->getMock();

        $mockCasrecVerificationService = m::mock('\App\Service\PreRegistrationVerificationService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isMultiDeputyCase')->with('12341234')->andReturn(false)
            ->shouldReceive('getLastMatchedDeputyNumbers')->andReturn(['123'])
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em, $mockCasrecVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfDeputyExists()
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $client = m::mock(Client::class)
            ->shouldReceive('hasDeputies')->andReturn(true)
            ->shouldReceive('getOrganisation')->andReturn(null)
            ->shouldReceive('getCaseNumber')->andReturn('12341234')
            ->getMock();

        $clientRepo = m::mock(ClientRepository::class)
            ->shouldReceive('findOneByCaseNumber')->andReturn($client)
            ->getMock();

        $userRepo = m::mock(UserRepository::class)
            ->shouldReceive('findOneByEmail')->andReturn(null)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($userRepo)
            ->getMock();

        $casrecVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $casrecVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfOrganisationExists()
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $client = m::mock(Client::class)
            ->shouldReceive('hasDeputies')->andReturn(false)
            ->shouldReceive('getOrganisation')->andReturn(new Organisation())
            ->shouldReceive('getCaseNumber')->andReturn('12341234')
            ->getMock();

        $clientRepo = m::mock(ClientRepository::class)
            ->shouldReceive('findOneByCaseNumber')->andReturn($client)
            ->getMock();

        $userRepo = m::mock(UserRepository::class)
            ->shouldReceive('findOneByEmail')->andReturn(null)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($userRepo)
            ->getMock();

        $casrecVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $casrecVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
