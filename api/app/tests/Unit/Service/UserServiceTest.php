<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\UserService;
use OPG\Digideps\Backend\v2\DTO\InviteeDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{
    private User $user;
    private EntityManager&MockObject $em;
    private ClientRepository&MockObject $clientRepository;
    private UserRepository&MockObject $userRepository;
    private UserService $sut;

    public function setUp(): void
    {
        $this->user = new User();
        $this->user->setRoleName(User::ROLE_LAY_DEPUTY);

        $client = new Client();
        $client->addUser($this->user);
        $client->setCaseNumber('12345678');
        $client->setCourtDate(new \DateTime('2014-06-06'));

        $this->em = self::createMock(EntityManager::class);
        $this->clientRepository = self::createMock(ClientRepository::class);
        $this->userRepository = self::createMock(UserRepository::class);

        $this->em->method('getRepository')->with(Client::class)->willReturn($this->clientRepository);

        $this->sut = new UserService($this->em, $this->clientRepository, $this->userRepository);
    }

    /**
     * Provides logged-in user role and expected registration route that should be set.
     */
    public static function setRoleForLoggedInUser(): array
    {
        return [
            [User::ROLE_LAY_DEPUTY, User::CO_DEPUTY_INVITE, 100, 1],
            [User::ROLE_ADMIN, User::ADMIN_INVITE, null, 0],
            [User::ROLE_PROF_ADMIN, User::ORG_ADMIN_INVITE, null, 0],
        ];
    }

    #[DataProvider('setRoleForLoggedInUser')]
    public function testRegistrationRoute(string $role, string $expectedRoute, ?int $clientId, int $numSaveUserToClient): void
    {
        $loggedInUser = $this->user;
        $loggedInUser->setRoleName($role);

        $userToAdd = new User();

        $userToAdd->setEmail('test@tester.co.uk');
        $this->em->expects(self::atLeastOnce())->method('persist');
        $this->em->expects(self::atLeastOnce())->method('flush');
        $this->userRepository->expects(self::atLeastOnce())->method('findOneBy')->with(['email' => 'test@tester.co.uk']);
        $this->clientRepository->expects(self::exactly($numSaveUserToClient))
            ->method('saveUserToClient')
            ->with($userToAdd, $clientId);

        $this->sut->addUser($loggedInUser, $userToAdd, $clientId);

        $this->assertEquals($expectedRoute, $userToAdd->getRegistrationRoute());
    }

    // get or add a user when an existing user with the same email address already exists
    public function testGetOrAddUserExistingUser(): void
    {
        $clientId = 99919992;

        $invitee = new InviteeDto('foo@bar.com', 'Karban', 'Steelcore');

        $existingUser = self::createMock(User::class);
        $existingUser->method('getRegistrationToken')->willReturn(null);
        $existingUser->expects(self::atLeastOnce())->method('recreateRegistrationToken');
        $existingUser->expects(self::atLeastOnce())->method('setActive')->with(true);

        $this->userRepository->expects(self::atLeastOnce())->method('findOneBy')
            ->with(['email' => 'foo@bar.com'])
            ->willReturn($existingUser);

        $this->clientRepository->expects(self::atLeastOnce())
            ->method('saveUserToClient')
            ->with($existingUser, $clientId);

        $this->em->expects(self::once())->method('persist')->with($existingUser);
        $this->em->expects(self::atLeastOnce())->method('flush');

        $user = $this->sut->getOrAddUser($invitee, $this->user, 12345667, $clientId);

        self::assertEquals($existingUser, $user);
    }

    // get or add a user when an existing user with the same deputy UID (but different email) exists
    public function testGetOrAddUserPrimaryExists(): void
    {
        $deputyUid = 9988553311;
        $clientId = 1837478367;
        $invitee = new InviteeDto('foo@bar.com', 'Karban', 'Steelcore');

        $existingUser = self::createMock(User::class);

        $this->userRepository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->with(new IsType(IsType::TYPE_ARRAY))
            ->willReturnCallback(function (array $criteria) use ($deputyUid, $existingUser) {
                if (
                    $criteria['deputyUid'] ?? $deputyUid === null
                    && $criteria['active'] ?? null === true
                    && $criteria['isPrimary'] ?? null === true
                ) {
                    return $existingUser;
                }

                // if user is searched for by anything other than the above, return null
                return null;
            });

        $this->em->expects(self::atLeastOnce())->method('persist')->with(self::isInstanceOf(User::class));
        $this->em->expects(self::atLeastOnce())->method('flush');

        $this->clientRepository->expects(self::once())
            ->method('saveUserToClient')
            ->with(self::isInstanceOf(User::class), $clientId);

        $user = $this->sut->getOrAddUser($invitee, $this->user, $deputyUid, $clientId);

        self::assertEquals($deputyUid, $user->getDeputyUid());
    }
}
