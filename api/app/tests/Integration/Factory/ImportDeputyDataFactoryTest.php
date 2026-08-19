<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Factory;

use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Factory\ImportDeputyDataFactory;
use OPG\Digideps\Backend\Factory\ImportOrganisationDataFactory;
use OPG\Digideps\Common\Deputy\DeputyType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class ImportDeputyDataFactoryTest extends ApiIntegrationTestCase
{
    use StagingFixtureTrait;

    public function testRun(): void
    {
        self::purgeDatabase();
        $repository = self::$entityManager->getRepository(Deputy::class);
        $this->assertEmpty($repository->findAll());

        $pro1 = $this->paProFixture(1, DeputyType::PRO);
        $pa1 = $this->paProFixture(2, DeputyType::PA);
        $lay1 = $this->layFixture(3);

        self::$entityManager->persist($pro1);
        self::$entityManager->persist($pa1);
        self::$entityManager->persist($lay1);

        self::$entityManager->flush();

        $organisationFactory = new ImportOrganisationDataFactory(self::$entityManager, new ParameterBag([
            'shared_email_domains' => ['gmail.com', 'hotmail.com']
        ]));
        $organisationFactory->run(false);

        $factory = new ImportDeputyDataFactory(self::$entityManager);
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(3, $all);
        foreach ($all as $deputy) {
            match ($deputy->getFirstname()) {
                'DeputyFirst1' => $this->assertSame(DeputyType::PRO, $deputy->getDeputyType()),
                'DeputyFirst2' => $this->assertSame(DeputyType::PA, $deputy->getDeputyType()),
                'DeputyFirst3' => $this->assertSame(DeputyType::LAY, $deputy->getDeputyType()),
                default => $this->fail('Unknown Deputy')
            };
        }

        self::$entityManager->persist($this->layFixture(4));

        new \ReflectionObject($pa1)->getProperty('deputyLastName')->setValue($pa1, 'Hello World');
        self::$entityManager->flush();
        self::$entityManager->clear();
        $factory->run(false);

        $all = $repository->findAll();
        $this->assertCount(4, $all);
        foreach ($all as $deputy) {
            match ($deputy->getFirstname()) {
                'DeputyFirst1' => $this->assertSame(DeputyType::PRO, $deputy->getDeputyType()),
                'DeputyFirst2' => $this->assertSame('Hello World', $deputy->getLastname()),
                'DeputyFirst3', 'DeputyFirst4' => $this->assertSame(DeputyType::LAY, $deputy->getDeputyType()),
                default => $this->fail('Unknown Deputy')
            };
        }
    }
}
