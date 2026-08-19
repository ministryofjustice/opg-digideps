<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Factory\ImportClientDataFactory;
use OPG\Digideps\Common\Deputy\DeputyType;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class ImportClientDataFactoryTest extends ApiIntegrationTestCase
{
    use StagingFixtureTrait;

    public function testRun(): void
    {
        self::purgeDatabase();
        $repository = self::$entityManager->getRepository(Client::class);
        $this->assertEmpty($repository->findAll());

        $pro1 = $this->paProFixture(1, DeputyType::PRO);
        $pa1 = $this->paProFixture(2, DeputyType::PA);
        $lay1 = $this->layFixture(3);

        self::$entityManager->persist($pro1);
        self::$entityManager->persist($pa1);
        self::$entityManager->persist($lay1);

        self::$entityManager->flush();


        $factory = new ImportClientDataFactory(self::$entityManager);
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(3, $all);
        foreach ($all as $client) {
            match ($client->getFirstname()) {
                'ClientFirst1' => $this->assertSame('ClientLast1', $client->getLastname()),
                'ClientFirst2' => $this->assertSame('ClientLast2', $client->getLastname()),
                'ClientFirst3' => $this->assertSame('ClientLast3', $client->getLastname()),
                default => $this->fail('Unknown Client')
            };
        }

        self::$entityManager->persist($this->layFixture(4));

        new \ReflectionObject($pa1)->getProperty('clientLastName')->setValue($pa1, 'Hello World');
        self::$entityManager->flush();
        self::$entityManager->clear();
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(4, $all);
        foreach ($all as $client) {
            match ($client->getFirstname()) {
                'ClientFirst1' => $this->assertSame('ClientLast1', $client->getLastname()),
                'ClientFirst2' => $this->assertSame('Hello World', $client->getLastname()),
                'ClientFirst3' => $this->assertSame('ClientLast3', $client->getLastname()),
                'ClientFirst4' => $this->assertSame('ClientLast4', $client->getLastname()),
                default => $this->fail('Unknown Client')
            };
        }
    }
}
