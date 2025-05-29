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
use Mockery as m;
use PHPUnit\Framework\TestCase;

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
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($mockUserRepository)
            ->getMock();

        $mockPreRegVerificationService = m::mock(PreRegistrationVerificationService::class);
        $mockPreRegVerificationService->shouldIgnoreMissing();

        $this->userRegistrationService = new UserRegistrationService($em, $mockPreRegVerificationService);
    }

    /**
     * @test
     */
    public function renderRegistrationHtmlEmail()
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientFirstname('Zac');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');
        $data->setPostcode('AB12CD');

        $mockUser = m::mock('\App\Entity\User')
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockUser->shouldReceive('setCreatedBy')->with($mockUser);

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

        $mockPreRegistrationVerificationService = m::mock('\App\Service\PreRegistrationVerificationService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isMultiDeputyCase')->with('12341234')->andReturn(false)
            ->shouldReceive('getLastMatchedDeputyNumbers')->andReturn(['123'])
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em, $mockPreRegistrationVerificationService);
        $selfRegisteredUser = $this->userRegistrationService->selfRegisterUser($data);
        self::assertEquals(User::SELF_REGISTER, $selfRegisteredUser->getRegistrationRoute());
        self::assertTrue($selfRegisteredUser->getPreRegisterValidatedDate() instanceof \DateTime);
    }

    public function testUserCannotRegisterIfClientExistsWithDeputies()
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
            ->shouldReceive('findByCaseNumber')->andReturn($client)
            ->getMock();

        $userRepo = m::mock(UserRepository::class)
            ->shouldReceive('findOneByEmail')->andReturn(null)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfClientExistsWithDeputiesCaseNumberWithT()
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('1234123T');

        $client = m::mock(Client::class)
            ->shouldReceive('hasDeputies')->andReturn(true)
            ->shouldReceive('getOrganisation')->andReturn(null)
            ->shouldReceive('getCaseNumber')->andReturn('1234123t')
            ->getMock();

        $clientRepo = m::mock(ClientRepository::class)
            ->shouldReceive('findByCaseNumber')->andReturn($client)
            ->getMock();

        $userRepo = m::mock(UserRepository::class)
            ->shouldReceive('findOneByEmail')->andReturn(null)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 1234123t already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
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
            ->shouldReceive('findByCaseNumber')->andReturn($client)
            ->getMock();

        $userRepo = m::mock(UserRepository::class)
            ->shouldReceive('findOneByEmail')->andReturn(null)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')->with('App\Entity\Client')->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with('App\Entity\User')->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
