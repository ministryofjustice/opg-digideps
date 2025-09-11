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

    private static OrganisationRepository $sut;
    private static Fixtures $fixtures;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$staticEntityManager);

        /** @var OrganisationRepository $sut */
        $sut = self::$staticEntityManager->getRepository(Organisation::class);
        self::$sut = $sut;
    }

    public function testGetAllArray()
    {
        self::$fixtures->createOrganisations(5);
        $this->entityManager->flush();

        self::assertCount(5, self::$sut->getAllArray());
    }

    public function testGetNonDeletedArray()
    {
        $orgs = self::$fixtures->createOrganisations(5);
        $this->entityManager->flush();

        $orgIds = array_map(fn (Organisation $org) => $org->getId(), $orgs);

        $nonDeletedOrgs = self::$sut->getNonDeletedArray();
        $nonDeletedOrgIds = array_map(fn (array $org) => $org['id'], $nonDeletedOrgs);

        foreach ($orgIds as $orgId) {
            self::assertContains($orgId, $nonDeletedOrgIds);
        }

        // check removing an org
        self::$fixtures->deleteOrganisation($orgs[0]->getId());
        $this->entityManager->flush();

        $orgIdsExceptDeleted = array_diff($orgIds, [$orgs[0]->getId()]);

        $nonDeletedOrgs = self::$sut->getNonDeletedArray();
        $nonDeletedOrgIds = array_map(fn (array $org) => $org['id'], $nonDeletedOrgs);

        foreach ($orgIdsExceptDeleted as $orgId) {
            self::assertContains($orgId, $nonDeletedOrgIds);
        }
    }

    public function testHasActiveEntitiesNoEntitiesInOrgReturnsFalse()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesSoftDeletedClientInOrgReturnsFalse()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = self::$fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientDeleted->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesArchivedClientInOrgReturnsFalse()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientArchived = self::$fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $this->entityManager->flush();

        self::$fixtures->addClientToOrganisation($clientArchived->getId(), $orgs[0]->getId());
        $this->entityManager->flush();
        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsInOrgReturnsFalse()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = self::$fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = self::$fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $clientDeleted->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertFalse($result);
    }

    public function testHasActiveEntitiesArchivedAndSoftDeletedAndActiveClientsInOrgReturnsTrue()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = self::$fixtures->createClient($user);
        $clientDeleted = self::$fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = self::$fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $clientActive->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $clientDeleted->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testHasActiveEntitiesArchivedAndSoftDeletedClientsAndUserInOrgReturnsTrue()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientDeleted = self::$fixtures->createClient($user, ['setDeletedAt' => new DateTime()]);
        $clientArchived = self::$fixtures->createClient($user, ['setArchivedAt' => new DateTime()]);
        $orgs[0]->addUser($user);
        $clientDeleted->setOrganisation($orgs[0]);
        $clientArchived->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testHasActiveEntitiesActiveClientAndUserInOrgReturnsTrue()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $user = self::$fixtures->createUser(roleName: User::ROLE_PA);
        $clientActive = self::$fixtures->createClient($user);
        $orgs[0]->addUser($user);
        $clientActive->setOrganisation($orgs[0]);
        $this->entityManager->flush();

        $result = self::$sut->hasActiveEntities($orgs[0]->getId());
        self::assertTrue($result);
    }

    public function testFindByEmailIdentifier()
    {
        $orgs = self::$fixtures->createOrganisations(1);
        $this->entityManager->flush();

        $result = self::$sut->findByEmailIdentifier($orgs[0]->getEmailIdentifier());
        self::assertEquals($orgs[0]->getId(), $result->getId());
    }
}
