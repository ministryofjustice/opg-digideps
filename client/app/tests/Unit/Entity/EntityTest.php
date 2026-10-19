<?php

declare(strict_types=1);

use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyEstimateCost;
use OPG\Digideps\Frontend\Entity\Report\ProfDeputyOtherCost;
use OPG\Digideps\Frontend\Entity\Report\ProfServiceFeeCurrent;
use PHPUnit\Framework\TestCase;
use Tests\OPG\Digideps\Frontend\Unit\Helpers\ObjectTester;

final class EntityTest extends TestCase
{
    public function testBankAccountValidOnConstruction(): void
    {
        $bankAccount = new BankAccount();
        $this->testEntity($bankAccount);
    }

    public function testProfDeputyEstimateCostValidOnConstruction(): void
    {
        $profDeputyEstimateCost = new ProfDeputyEstimateCost('foo', '10', true, 'bar');
        $this->testEntity($profDeputyEstimateCost);
    }

    public function testProfDeputyOtherCostValidOnConstruction(): void
    {
        $profDeputyOtherCost = new ProfDeputyOtherCost('foo', '10', true, 'bar');
        $this->testEntity($profDeputyOtherCost);
    }

    public function testProfServiceFeeCurrentValidOnConstruction(): void
    {
        $profServiceFeeCurrent = new ProfServiceFeeCurrent();
        $this->testEntity($profServiceFeeCurrent);
    }

    private function testEntity(object $entity): void
    {
        self::assertSame([], ObjectTester::testObjectWithReflection($entity, new ReflectionClass($entity)));
    }
}
