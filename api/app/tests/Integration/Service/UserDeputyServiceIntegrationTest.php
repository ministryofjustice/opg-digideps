<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\PreRegistration;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserDeputyService;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiIntegrationTestCase;

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

    public function testAddMissingUserDeputies(): void
    {
        $existingDeputyUid = '19847384';
        $nonExistentDeputyUid = '46237278';
        $user2Email = 'bok.mansp@some.where.org';

        // existing deputy
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $existingDeputyUid);
        self::$entityManager->persist($deputy);

        // a user referencing the deputy UID which already exists
        $user1 = UserTestHelper::createUser(deputyUid: intval($existingDeputyUid));
        self::$entityManager->persist($user1);

        // a user referencing a deputy UID which doesn't exist
        $user2 = UserTestHelper::createUser(email: $user2Email, deputyUid: intval($nonExistentDeputyUid));
        self::$entityManager->persist($user2);

        // pre-reg entries for both deputy UIDs
        $preReg1 = new PreRegistration(['DeputyUid' => $existingDeputyUid]);
        self::$entityManager->persist($preReg1);

        $preReg2 = new PreRegistration(['DeputyUid' => $nonExistentDeputyUid]);
        self::$entityManager->persist($preReg2);

        self::$entityManager->flush();

        // test
        $this->sut->addMissingUserDeputies();

        // check that $user1 is associated with existing $deputy
        /** @var User $user1 */
        $user1 = $this->userRepository->findOneBy(['deputyUid' => $existingDeputyUid]);
        self::assertNotNull($user1);
        self::assertEquals($deputy, $user1->getDeputy());

        // check that a deputy was created for $user2 and that they are associated
        /** @var User $user2 */
        $user2 = $this->userRepository->findOneBy(['deputyUid' => $nonExistentDeputyUid]);
        self::assertNotNull($user2);

        $newDeputy = $user2->getDeputy();
        self::assertEquals($nonExistentDeputyUid, $newDeputy->getDeputyUid());

        // email1 should match the user's email
        self::assertEquals($user2Email, $newDeputy->getEmail1());
    }
}
