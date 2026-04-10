<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityRepository;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\PreRegistrationVerificationService;
use OPG\Digideps\Backend\Service\UserRegistrationService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UserRegistrationServiceTest extends TestCase
{
    private UserRegistrationService $userRegistrationService;

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

    #[Test]
    public function renderRegistrationHtmlEmail(): void
    {
        $data = new SelfRegisterData();

        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientFirstname('Zac');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');
        $data->setPostcode('AB12CD');

        $mockUser = m::mock(User::class)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $mockUser->shouldReceive('setCreatedBy')->with($mockUser);

        $mockClient = m::mock(Client::class)
            ->shouldIgnoreMissing(true)
            ->makePartial()
            ->shouldReceive('getCourtDate')->andReturn(new DateTime('2015-05-04'))
            ->getMock();

        $datetime = new DateTime('2015-05-04');
        $mockClient->shouldIgnoreMissing(true)
            ->shouldReceive('getCourtDate')->andReturn($datetime)
            ->getMock();

        $mockConnection = m::mock(Connection::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('beginTransaction')
            ->shouldReceive('commit')
            ->getMock();

        $mockUserRepository = m::mock(EntityRepository::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('findOneByEmail')->with('zac@thetolleys.com')->andReturn(null)
            ->getMock();

        $mockClientRepository = m::mock(EntityRepository::class)
            ->shouldIgnoreMissing(false)
            ->shouldReceive('findOneBy')->withAnyArgs()->andReturn(false)
            ->getMock();

        $em = m::mock(EntityManager::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getConnection')->andReturn($mockConnection)
            ->shouldReceive('flush')->twice()
            ->shouldReceive('persist')->with($mockUser)
            ->shouldReceive('persist')->with($mockClient)
            ->shouldReceive('getRepository')->with(User::class)->andReturn($mockUserRepository)
            ->shouldReceive('getRepository')->with(Client::class)->andReturn($mockClientRepository)
            ->getMock();

        $mockPreRegistrationVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldIgnoreMissing(true)
            ->shouldReceive('validate')->andReturn([new PreRegistration([])])
            ->shouldReceive('isMultiDeputyCase')->with('12341234')->andReturn(false)
            ->shouldReceive('getLastMatchedDeputyNumbers')->andReturn(['123'])
            ->getMock();

        $this->userRegistrationService = new UserRegistrationService($em, $mockPreRegistrationVerificationService);
        $selfRegisteredUser = $this->userRegistrationService->selfRegisterUser($data);

        self::assertEquals(User::SELF_REGISTER, $selfRegisteredUser->getRegistrationRoute());
        self::assertTrue($selfRegisteredUser->getPreRegisterValidatedDate() instanceof DateTime);
    }

    public function testUserCannotRegisterIfClientExistsWithDeputies(): void
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
            ->shouldReceive('getRepository')->with(Client::class)->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with(User::class)->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfClientExistsWithDeputiesCaseNumberWithT(): void
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
            ->shouldReceive('getRepository')->with(Client::class)->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with(User::class)->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 1234123t already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfOrganisationExists(): void
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
            ->shouldReceive('getRepository')->with(Client::class)->andReturn($clientRepo)
            ->shouldReceive('getRepository')->with(User::class)->andReturn($userRepo)
            ->getMock();

        $preRegVerificationService = m::mock(PreRegistrationVerificationService::class)
            ->shouldReceive('isMultiDeputyCase')->andReturn(false)
            ->getMock();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $this->userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $this->userRegistrationService->selfRegisterUser($data);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
