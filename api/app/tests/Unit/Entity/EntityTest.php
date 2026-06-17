<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Report\AssetOther;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testDeputyValidOnConstruction(): void
    {
        $deputy = new Deputy('', DeputyType::LAY, '', '');
        $this->testEntity($deputy);
    }

    public function testReportValidOnConstruction(): void
    {
        $report = $this->makeReport();
        $this->testEntity($report);
    }

    public function testClientValidOnConstruction(): void
    {
        $client = new Client();
        $this->testEntity($client);
    }

    public function testContactValidOnConstruction(): void
    {
        $contact = new Contact($this->makeReport());
        $this->testEntity($contact);
    }

    public function testAssetOtherValidOnConstruction(): void
    {
        $assetOther = new AssetOther($this->makeReport());
        $this->testEntity($assetOther);
    }

    public function testAssetPropertyValidOnConstruction(): void
    {
        $assetProperty = new AssetProperty($this->makeReport());
        $this->testEntity($assetProperty);
    }

    public function testBankAccountValidOnConstruction(): void
    {
        $bankAccount = new BankAccount($this->makeReport());
        $this->testEntity($bankAccount);
    }

    private function testEntity(object $entity): void
    {
        $this->assertSame([], $this->testEntityWithReflection($entity, new \ReflectionClass($entity)));
    }

    private function makeReport(): Report
    {
        return new Report(new Client(), '102', new \DateTime(), new \DateTime(), false);
    }

    /**
     * @template T of object
     * @param T $entity
     * @param \ReflectionClass<T> $reflection
     */
    private function testEntityWithReflection(object $entity, \ReflectionClass $reflection): array
    {
        $errors = [];
        foreach ($reflection->getProperties() as $property) {
            try {
                $_ = $property->getValue($entity);
                if ($property->getType() === null) {
                    $errors[] = [
                        $property->getName(),
                        $property->getDeclaringClass()->getName(),
                        ''
                    ];
                }
            } catch (\Throwable $_) {
                $errors[] = [
                    $property->getName(),
                    $property->getDeclaringClass()->getName(),
                    "{$property->getType()}"
                ];
            }
        }

        if ($reflection->getParentClass()) {
            $errors = [...$this->testEntityWithReflection($entity, $reflection->getParentClass()), ...$errors];
        }

        return $errors;
    }
}
