<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use PHPUnit\Framework\Attributes\Test;
use DateTime;
use App\Entity\Organisation;
use App\Entity\User;
use App\Repository\OrganisationRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\Mapping\ClassMetadata;
use Prophecy\PhpUnit\ProphecyTrait;

final class OrganisationRepositoryTest extends ApiBaseTestCase
{
    use ProphecyTrait;

    private OrganisationRepository $sut;
    private Fixtures $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        $metaClass = self::prophesize(ClassMetadata::class);
        $metaClass->name = Organisation::class;

        /** @var OrganisationRepository $sut */
        $sut = $this->entityManager->getRepository(Organisation::class);
        $this->sut = $sut;

        $this->purgeDatabase();
    }

    #[Test]
    public function testGetAllArray(): void
    {
        $this->fixtures->createOrganisations(5);
        $this->entityManager->flush();

        self::assertCount(5, $this->sut->getAllArray());
    }

    #[Test]
    public function testGetNonDeletedArray(): void
    {
        $orgs = $this->fixtures->createOrganisations(5);
        $this->entityManager->flush();

        $allOrgs = $this->sut->getNonDeletedArray();
        self::assertCount(5, $allOrgs);

        $this->fixtures->deleteOrganisation($orgs[0]->getId());
        $this->entityManager->flush();

        $nonDeletedOrgs = $this->sut->getNonDeletedArray();
        self::assertCount(4, $nonDeletedOrgs);
    }

    #[Test]
    public function testHasActiveEntitiesNoEntitiesInOrgReturnsFalse(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    #[Test]
    public function testHasActiveEntitiesSoftDeletedClientInOrgReturnsFalse(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    #[Test]
    public function testHasActiveEntitiesArchivedClientInOrgReturnsFalse(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    #[Test]
    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsInOrgReturnsFalse(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    #[Test]
    public function testHasActiveEntitiesArchivedAndSoftDeletedAndActiveClientsInOrgReturnsTrue(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientActive->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    #[Test]
    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsAndUserInOrgReturnsTrue(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addUserToOrganisation($user->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    #[Test]
    public function testHasActiveEntitiesActiveClientAndUserInOrgReturnsTrue(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $this->entityManager->flush();

        $this->fixtures->addUserToOrganisation($user->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientActive->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    #[Test]
    public function testFindByEmailIdentifier(): void
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->findByEmailIdentifier($orgs[0]->getEmailIdentifier());
        self::assertEquals($orgs[0], $result);
    }
}
