<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Entity;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testDeputyValidOnConstruction(): void
    {
        $deputy = new Deputy('', DeputyType::LAY, '', '');
        $this->testEntity($deputy);
    }

    private function testEntity(object $entity): void
    {
        $errors = [];
        $reflection = new \ReflectionClass($entity);
        foreach ($reflection->getProperties() as $property) {
            try {
                $_ = $property->getValue($entity);
            } catch (\Throwable $throwable) {
                $errors[] = [
                    $property->getName(),
                    $property->getDeclaringClass()->getName()
                ];
            }
        }
        $this->assertSame([], $errors);
    }
}
