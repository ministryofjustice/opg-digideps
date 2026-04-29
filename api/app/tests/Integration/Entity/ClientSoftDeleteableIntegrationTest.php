<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ClientRepository;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class ClientSoftDeleteableIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static ClientRepository $repository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var ClientRepository $repository */
        $repository = self::$entityManager->getRepository(Client::class);
        self::$repository = $repository;
    }

    public function testSoftDeleteSetsDeletedAt(): void
    {
        $user = self::$fixtures->createUser(roleName: User::ROLE_LAY_DEPUTY);
        $client = self::$fixtures->createClient($user);
        self::$entityManager->flush();

        $clientId = $client->getId();
        self::assertNull($client->getDeletedAt(), 'deletedAt should be null before deletion');

        // Soft delete via Gedmo: remove the entity through the EntityManager
        self::$entityManager->remove($client);
        self::$entityManager->flush();

        // NB if clear is not called after a soft delete, the cached copy of the object
        // is returned, which breaks this test
        self::$entityManager->clear();

        // With the Gedmo filter active, the entity should no longer be found by normal queries
        $found = self::$repository->find($clientId);
        self::assertNull($found, 'Soft-deleted client should not be returned by default queries');
    }

    public function testSoftDeletedClientIsVisibleWithFilterDisabled(): void
    {
        $user = self::$fixtures->createUser(roleName: User::ROLE_LAY_DEPUTY);
        $client = self::$fixtures->createClient($user);
        self::$entityManager->flush();

        $clientId = $client->getId();

        self::$entityManager->remove($client);
        self::$entityManager->flush();
        self::$entityManager->clear();

        // Disable the Gedmo soft-deleteable filter to query deleted records
        self::$entityManager->getFilters()->disable('softdeleteable');

        try {
            $deletedClient = self::$repository->find($clientId);

            self::assertNotNull($deletedClient, 'Soft-deleted client should be visible when filter is disabled');
            self::assertNotNull($deletedClient->getDeletedAt(), 'deletedAt should be set after soft deletion');
        } finally {
            self::$entityManager->getFilters()->enable('softdeleteable');
        }
    }

    public function testRestoreSoftDeletedClient(): void
    {
        $user = self::$fixtures->createUser(roleName: User::ROLE_LAY_DEPUTY);
        $client = self::$fixtures->createClient($user);
        self::$entityManager->flush();

        $clientId = $client->getId();

        // Soft delete
        self::$entityManager->remove($client);
        self::$entityManager->flush();
        self::$entityManager->clear();

        // Verify it's hidden from normal queries
        $shouldBeNull = self::$repository->find($clientId);
        self::assertNull($shouldBeNull, 'Client should be hidden after soft deletion');

        // Restore: disable filter, find and clear deletedAt, re-enable filter
        self::$entityManager->getFilters()->disable('softdeleteable');

        try {
            $deletedClient = self::$repository->find($clientId);
            self::assertNotNull($deletedClient, 'Client must be found with filter disabled to restore it');

            $deletedClient->setDeletedAt(null);
            self::$entityManager->flush();
        } finally {
            self::$entityManager->getFilters()->enable('softdeleteable');
        }

        self::$entityManager->clear();

        // Verify it's visible again in normal queries
        $restoredClient = self::$repository->find($clientId);
        self::assertNotNull($restoredClient, 'Restored client should be visible in normal queries');
        self::assertNull($restoredClient->getDeletedAt(), 'deletedAt should be null after restore');
    }
}
