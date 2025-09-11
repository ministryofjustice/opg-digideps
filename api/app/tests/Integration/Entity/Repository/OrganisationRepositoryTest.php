<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use DateTime;
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
    }

    public function testGetAllArray()
    {
        $this->fixtures->createOrganisations(5);
        $this->entityManager->flush();

        self::assertCount(5, $this->sut->getAllArray());
    }

    public function testGetNonDeletedArray()
    {
        $orgs = $this->fixtures->createOrganisations(5);
        $this->entityManager->flush();

        $orgIds = array_map(fn (Organisation $org) => $org->getId(), $orgs);

        $nonDeletedOrgs = $this->sut->getNonDeletedArray();
        $nonDeletedOrgIds = array_map(fn (array $org) => $org['id'], $nonDeletedOrgs);

        foreach ($orgIds as $orgId) {
            self::assertContains($orgId, $nonDeletedOrgIds);
        }

        // check removing an org
        $this->fixtures->deleteOrganisation($orgs[0]->getId());
        $this->entityManager->flush();

        $orgIdsExceptDeleted = array_diff($orgIds, [$orgs[0]->getId()]);

        $nonDeletedOrgs = $this->sut->getNonDeletedArray();
        $nonDeletedOrgIds = array_map(fn (array $org) => $org['id'], $nonDeletedOrgs);

        foreach ($orgIdsExceptDeleted as $orgId) {
            self::assertContains($orgId, $nonDeletedOrgIds);
        }
    }

    public function testHasActiveEntitiesNoEntitiesInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesSoftDeletedClientInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientDeleted->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesArchivedClientInOrgReturnsFalse()
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

    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsInOrgReturnsFalse()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $clientDeleted->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesArchivedAndSoftDeletedAndActiveClientsInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $clientActive->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $clientDeleted->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsAndUserInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = $this->fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = $this->fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $orgs[0]->addUser($user);
        $clientDeleted->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testHasActiveEntitiesActiveClientAndUserInOrgReturnsTrue()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $user = $this->fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = $this->fixtures->createClient($user);
        $orgs[0]->addUser($user);
        $clientActive->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = $this->sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testFindByEmailIdentifier()
    {
        $orgs = $this->fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = $this->sut->findByEmailIdentifier($orgs[0]->getEmailIdentifier());
        self::assertEquals($orgs[0]->getId(), $result->getId());
    }
}
