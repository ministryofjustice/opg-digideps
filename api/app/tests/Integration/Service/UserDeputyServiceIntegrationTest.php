<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserDeputyService;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiIntegrationTestCase;

class UserDeputyServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static array $userEmails = [
        'lay-with-deputy' => 'uds.vel.smik.lay@some.where.org',
        'lay-without-deputy' => 'uds.bok.mansp.lay@some.where.org',
    ];

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

    // NB this test will add deputies for all users, which includes users added as fixtures
    public function testAddMissingLayUserDeputies(): void
    {
        $existingDeputyUid = '19847384';
        $nonExistentDeputyUid = '46237278';

        // existing deputy
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $existingDeputyUid);
        self::$entityManager->persist($deputy);

        // a lay user referencing a deputy UID which already exists in the deputy table
        $user1 = UserTestHelper::createUser(
            email: self::$userEmails['lay-with-deputy'],
            deputyUid: intval($existingDeputyUid)
        );
        self::$entityManager->persist($user1);

        // a lay user referencing a deputy UID which doesn't exist in the deputy table
        $user2 = UserTestHelper::createUser(
            email: self::$userEmails['lay-without-deputy'],
            deputyUid: intval($nonExistentDeputyUid)
        );
        self::$entityManager->persist($user2);

        self::$entityManager->flush();

        // test
        $this->sut->addMissingUserDeputies();

        // check that $user1 is associated with existing $deputy
        /** @var User $user1 */
        $user1 = $this->userRepository->findOneBy(['deputyUid' => $existingDeputyUid]);
        self::assertNotNull($user1);
        self::assertEquals($deputy, $user1->getDeputy());
        self::assertEquals($user1, $deputy->getUser());

        // check that a deputy was created for $user2 and that they are associated
        /** @var User $user2 */
        $user2 = $this->userRepository->findOneBy(['deputyUid' => $nonExistentDeputyUid]);
        self::assertNotNull($user2);

        $newDeputy = $user2->getDeputy();
        self::assertEquals($nonExistentDeputyUid, $newDeputy->getDeputyUid());
        self::assertEquals($user2, $newDeputy->getUser());

        // deputy's email1 should match the user's email
        self::assertEquals(self::$userEmails['lay-without-deputy'], $newDeputy->getEmail1());
    }
}
