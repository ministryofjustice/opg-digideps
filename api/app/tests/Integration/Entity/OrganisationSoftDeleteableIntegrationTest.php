<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity;

use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Repository\OrganisationRepository;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class OrganisationSoftDeleteableIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static OrganisationRepository $repository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var OrganisationRepository $repository */
        $repository = self::$entityManager->getRepository(Organisation::class);
        self::$repository = $repository;
    }

    public function testSoftDeleteSetsDeletedAt(): void
    {
        $org = self::$fixtures->createOrganisation('Test Org', 'softdelete@example.com', true);
        self::$entityManager->flush();

        $orgId = $org->getId();
        self::assertNull($org->getDeletedAt(), 'deletedAt should be null before deletion');

        // Soft delete via Gedmo: remove the entity through the EntityManager
        self::$entityManager->remove($org);
        self::$entityManager->flush();

        // NB if clear is not called after a soft delete, the cached copy of the object
        // is returned, which breaks this test
        self::$entityManager->clear();

        // With the Gedmo filter active, the entity should no longer be found by normal queries
        $found = self::$repository->find($orgId);
        self::assertNull($found, 'Soft-deleted organisation should not be returned by default queries');
    }

    public function testSoftDeletedOrganisationIsVisibleWithFilterDisabled(): void
    {
        $org = self::$fixtures->createOrganisation('Test Org Visible', 'softdelete-visible@example.com', true);
        self::$entityManager->flush();

        $orgId = $org->getId();

        self::$entityManager->remove($org);
        self::$entityManager->flush();
        self::$entityManager->clear();

        // Disable the Gedmo soft-deleteable filter to query deleted records
        self::$entityManager->getFilters()->disable('softdeleteable');

        try {
            $deletedOrg = self::$repository->find($orgId);

            self::assertNotNull($deletedOrg, 'Soft-deleted organisation should be visible when filter is disabled');
            self::assertNotNull($deletedOrg->getDeletedAt(), 'deletedAt should be set after soft deletion');
        } finally {
            self::$entityManager->getFilters()->enable('softdeleteable');
        }
    }

    public function testRestoreSoftDeletedOrganisation(): void
    {
        $org = self::$fixtures->createOrganisation('Test Org Restore', 'softdelete-restore@example.com', true);
        self::$entityManager->flush();

        $orgId = $org->getId();

        // Soft delete
        self::$entityManager->remove($org);
        self::$entityManager->flush();
        self::$entityManager->clear();

        // Verify it's hidden from normal queries
        $shouldBeNull = self::$repository->find($orgId);
        self::assertNull($shouldBeNull, 'Organisation should be hidden after soft deletion');

        // Restore: disable filter, find and clear deletedAt, re-enable filter
        self::$entityManager->getFilters()->disable('softdeleteable');

        try {
            $deletedOrg = self::$repository->find($orgId);
            self::assertNotNull($deletedOrg, 'Organisation must be found with filter disabled to restore it');

            $deletedOrg->setDeletedAt(null);
            self::$entityManager->flush();
        } finally {
            self::$entityManager->getFilters()->enable('softdeleteable');
        }

        self::$entityManager->clear();

        // Verify it's visible again in normal queries
        $restoredOrg = self::$repository->find($orgId);
        self::assertNotNull($restoredOrg, 'Restored organisation should be visible in normal queries');
        self::assertNull($restoredOrg->getDeletedAt(), 'deletedAt should be null after restore');
        self::assertEquals('Test Org Restore', $restoredOrg->getName());
    }
}
