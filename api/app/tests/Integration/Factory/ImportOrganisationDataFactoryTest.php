<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory;

use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Factory\ImportOrganisationDataFactory;
use OPG\Digideps\Common\Deputy\DeputyType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class ImportOrganisationDataFactoryTest extends ApiIntegrationTestCase
{
    use StagingFixtureTrait;

    public function testRun(): void
    {
        self::purgeDatabase();
        $repository = self::$entityManager->getRepository(Organisation::class);
        $this->assertEmpty($repository->findAll());

        self::$entityManager->persist(
            $this->paProFixture(
                1,
                DeputyType::PRO,
                email: 'xyz@pro1.com'
            )
        );
        $org2 = $this->paProFixture(
            2,
            DeputyType::PRO,
            email: 'xyz@pro2.com'
        );
        self::$entityManager->persist($org2);
        self::$entityManager->persist(
            $this->paProFixture(
                3,
                DeputyType::PA,
                email: 'xyz@pa1.com'
            )
        );
        self::$entityManager->persist(
            $this->paProFixture(
                4,
                DeputyType::PRO,
                email: 'xyz@gmail.com'
            )
        );
        self::$entityManager->persist(
            $this->paProFixture(
                5,
                DeputyType::PA,
                email: 'xyz@hotmail.com'
            )
        );
        self::$entityManager->flush();

        $factory = new ImportOrganisationDataFactory(self::$entityManager, new ParameterBag([
            'shared_email_domains' => ['gmail.com', 'hotmail.com']
        ]));
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(5, $all);
        foreach ($all as $organisation) {
            match ($organisation->getName()) {
                'Organisation 1' => $this->assertSame('pro1.com', $organisation->getEmailIdentifier()),
                'Organisation 2' => $this->assertSame('pro2.com', $organisation->getEmailIdentifier()),
                'Organisation 3' => $this->assertSame('pa1.com', $organisation->getEmailIdentifier()),
                'Organisation 4' => $this->assertSame('xyz@gmail.com', $organisation->getEmailIdentifier()),
                'Organisation 5' => $this->assertSame('xyz@hotmail.com', $organisation->getEmailIdentifier()),
                default => $this->fail('Unknown Organisation')
            };
        }

        self::$entityManager->persist(
            $this->paProFixture(
                6,
                DeputyType::PA,
                email: 'xyz@pa2.com'
            )
        );

        new \ReflectionObject($org2)->getProperty('deputyOrganisation')->setValue($org2, 'Hello World');
        self::$entityManager->flush();
        self::$entityManager->clear();

        $factory = new ImportOrganisationDataFactory(self::$entityManager, new ParameterBag([
            'shared_email_domains' => ['gmail.com', 'hotmail.com']
        ]));
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(6, $all);
        foreach ($all as $organisation) {
            match ($organisation->getName()) {
                'Organisation 1' => $this->assertSame('pro1.com', $organisation->getEmailIdentifier()),
                'Hello World' => $this->assertSame('pro2.com', $organisation->getEmailIdentifier()),
                'Organisation 3' => $this->assertSame('pa1.com', $organisation->getEmailIdentifier()),
                'Organisation 4' => $this->assertSame('xyz@gmail.com', $organisation->getEmailIdentifier()),
                'Organisation 5' => $this->assertSame('xyz@hotmail.com', $organisation->getEmailIdentifier()),
                'Organisation 6' => $this->assertSame('pa2.com', $organisation->getEmailIdentifier()),
                default => $this->fail('Unknown Organisation')
            };
        }
    }
}
