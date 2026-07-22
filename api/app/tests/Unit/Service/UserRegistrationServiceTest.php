<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\PreRegistrationVerificationService;
use OPG\Digideps\Backend\Service\UserRegistrationService;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class UserRegistrationServiceTest extends TestCase
{
    public function testUserRegistrationSuccess(): void
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientFirstname('Zac');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');
        $data->setPostcode('AB12CD');

        $em = $this->getStubEntityManager(null);
        $em->method('getConnection')->willReturn(self::createStub(Connection::class));

        $preregRecord = self::createConfiguredStub(PreRegistration::class, ['getDeputyUid' => '21214313']);

        $preRegVerificationService = self::createMock(PreRegistrationVerificationService::class);
        $preRegVerificationService->expects(self::once())->method('isMultiDeputyCase')->willReturn(false);
        $preRegVerificationService->expects(self::once())->method('validate')->willReturn([$preregRecord]);

        $userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $user = $userRegistrationService->selfRegisterUser($data);

        self::assertEquals('Zac', $user->getFirstname());
        self::assertEquals('Tolley', $user->getLastname());
        self::assertEquals('zac@thetolleys.com', $user->getEmail());
        self::assertEquals('21214313', $user->getDeputyUid());
        self::assertNotNull($user->getRegistrationToken());
        self::assertEquals(User::ROLE_LAY_DEPUTY, $user->getRoleName());
    }

    public function testUserCannotRegisterIfClientExistsWithDeputies(): void
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $client = self::createMock(Client::class);
        $client->expects($this->once())->method('hasDeputies')->willReturn(true);
        $client->expects($this->once())->method('getCaseNumber')->willReturn('12341234');

        $em = $this->getStubEntityManager($client);

        $preRegVerificationService = self::createMock(PreRegistrationVerificationService::class);
        $preRegVerificationService->expects(self::once())->method('isMultiDeputyCase')->willReturn(false);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfClientExistsWithDeputiesCaseNumberWithT(): void
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('1234123T');

        $client = self::createMock(Client::class);
        $client->expects(self::once())->method('hasDeputies')->willReturn(true);
        $client->expects(self::once())->method('getCaseNumber')->willReturn('1234123t');

        $em = $this->getStubEntityManager($client);

        $preRegVerificationService = self::createMock(PreRegistrationVerificationService::class);
        $preRegVerificationService->expects(self::once())->method('isMultiDeputyCase')->willReturn(false);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 1234123t already used');

        $userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $userRegistrationService->selfRegisterUser($data);
    }

    public function testUserCannotRegisterIfOrganisationExists(): void
    {
        $data = new SelfRegisterData();
        $data->setFirstname('Zac');
        $data->setLastname('Tolley');
        $data->setEmail('zac@thetolleys.com');
        $data->setClientLastname('Cross-Tolley');
        $data->setCaseNumber('12341234');

        $client = self::createMock(Client::class);
        $client->expects(self::once())->method('hasDeputies')->willReturn(false);
        $client->expects(self::once())->method('getOrganisation')->willReturn(new Organisation());
        $client->expects(self::once())->method('getCaseNumber')->willReturn('12341234');

        $preRegVerificationService = self::createMock(PreRegistrationVerificationService::class);
        $preRegVerificationService->expects(self::once())->method('isMultiDeputyCase')->willReturn(false);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('User registration: Case number 12341234 already used');

        $em = $this->getStubEntityManager($client);

        $userRegistrationService = new UserRegistrationService($em, $preRegVerificationService);
        $userRegistrationService->selfRegisterUser($data);
    }

    private function getStubEntityManager(?Client $client, ?User $user = null): EntityManager&Stub
    {
        $clientRepo = self::createMock(ClientRepository::class);
        $clientRepo->expects(self::once())->method('findByCaseNumber')->willReturn($client);

        $userRepo = self::createMock(UserRepository::class);
        $userRepo->expects(self::once())->method('findOneByEmail')->willReturn($user);

        $em = self::createStub(EntityManager::class);
        $em->method('getRepository')->willReturnCallback(function (string $class) use ($clientRepo, $userRepo) {
            return match ($class) {
                Client::class => $clientRepo,
                User::class => $userRepo,
                default => throw new \InvalidArgumentException("Unexpected class: $class"),
            };
        });

        return $em;
    }
}
