<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Service;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\Service\UserDeputyService;
use OPG\Digideps\Backend\TestHelpers\DeputyTestHelper;
use OPG\Digideps\Backend\TestHelpers\UserTestHelper;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserDeputyServiceIntegrationTest extends ApiIntegrationTestCase
{
    private UserRepository $userRepository;
    private UserDeputyService $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var UserRepository $repo */
        $repo = self::$entityManager->getRepository(User::class);
        $this->userRepository = $repo;

        /** @var UserDeputyService $sut */
        $sut = self::$container->get(UserDeputyService::class);
        $this->sut = $sut;
    }

    public static function roleDataProvider(): array
    {
        return [
            // email for user with deputy, UID for existing deputy, email for user without deputy,
            // UID for deputy that doesn't exist, role_name for test users
            ['uds.lay.vel@some.where.org', '79847384', 'uds.lay.smik@some.where.org', '96237276', User::ROLE_LAY_DEPUTY],
            ['uds.pa.bat@some.where.org', '76847385', 'uds.pa.lop@some.where.org', '96237277', User::ROLE_PA_NAMED],
            ['uds.pro.car@some.where.org', '76847386', 'uds.pro.lou@some.where.org', '96237278', User::ROLE_PROF_NAMED],
        ];
    }

    // NB this test will add deputies for all users, which includes users added as fixtures
    #[DataProvider('roleDataProvider')]
    public function testAddMissingUserDeputies(
        string $emailWithDeputy,
        string $existingDeputyUid,
        string $emailWithoutDeputy,
        string $nonExistentDeputyUid,
        string $roleName
    ): void {
        // existing deputy
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $existingDeputyUid);
        self::$entityManager->persist($deputy);

        // a lay user referencing the deputy UID which already exists in the deputy table,
        // but which is not associated with the deputy record
        $user1 = UserTestHelper::createUser(
            roleName: $roleName,
            email: $emailWithDeputy,
            deputyUid: intval($existingDeputyUid),
        );
        self::$entityManager->persist($user1);

        // a lay user referencing a deputy UID which doesn't exist in the deputy table
        $user2 = UserTestHelper::createUser(
            roleName: $roleName,
            email: $emailWithoutDeputy,
            deputyUid: intval($nonExistentDeputyUid)
        );
        self::$entityManager->persist($user2);

        self::$entityManager->flush();

        // test
        $this->sut->addMissingUserDeputies();

        // ensure that the existing deputy is up to date with database state
        self::$entityManager->refresh($deputy);

        // check that $user1 is associated with existing $deputy
        /** @var User $user1 */
        $user1 = $this->userRepository->findOneBy(['deputyUid' => $existingDeputyUid]);

        self::assertNotNull($user1);
        self::assertEquals($deputy->getId(), $user1->getDeputy()->getId());
        self::assertEquals($user1->getId(), $deputy->getUser()->getId());

        // check that a deputy was created for $user2 and that they are associated
        /** @var User $user2 */
        $user2 = $this->userRepository->findOneBy(['deputyUid' => $nonExistentDeputyUid]);
        self::assertNotNull($user2);

        $newDeputy = $user2->getDeputy();
        self::assertEquals($nonExistentDeputyUid, $newDeputy->getDeputyUid());
        self::assertEquals($user2->getId(), $newDeputy->getUser()->getId());

        // deputy's email1 should match the user's email
        self::assertEquals($emailWithoutDeputy, $newDeputy->getEmail1());
    }
}
