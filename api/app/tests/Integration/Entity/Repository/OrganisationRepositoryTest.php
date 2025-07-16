<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\Organisation;
use App\Entity\User;
use App\Repository\OrganisationRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\Mapping\ClassMetadata;
use Prophecy\PhpUnit\ProphecyTrait;

class OrganisationRepositoryTest extends ApiBaseTestCase
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

    /** @test */
    public function testGetAllArray()
    {
        $this->fixtures->createOrganisations(5);
        $this->entityManager->flush();

        self::assertCount(5, $this->sut->getAllArray());
    }

    /** @test */
    public function testGetNonDeletedArray()
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

    /** @test */
    public function testHasActiveEntitiesNoEntitiesInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    /** @test */
    public function testHasActiveEntitiesSoftDeletedClientInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new \DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    /** @test */
    public function testHasActiveEntitiesArchivedClientInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new \DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    /** @test */
    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new \DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new \DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    /** @test */
    public function testHasActiveEntitiesArchivedAndSoftDeletedAndActiveClientsInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new \DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new \DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($clientActive->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    /** @test */
    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsAndUserInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new \DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new \DateTime()]);
        $this->entityManager->flush();

        $this->fixtures->addUserToOrganisation($user->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientDeleted->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    /** @test */
    public function testHasActiveEntitiesActiveClientAndUserInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $this->entityManager->flush();

        $this->fixtures->addUserToOrganisation($user->getId(), $orgs[0]->getId());
        $this->fixtures->addClientToOrganisation($clientActive->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    /** @test */
    public function testFindByEmailIdentifier()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->findByEmailIdentifier($orgs[0]->getEmailIdentifier());
        self::assertEquals($orgs[0], $result);
    }
}
