<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class MoneyReceivedOnClientsBehalfTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider invalidDataProvider
     */
    public function testValidation($moneyType, $amount, $amountDontKnow, $whoReceived, $expectedViolationCount)
    {
        $sut = (new MoneyReceivedOnClientsBehalf())
            ->setMoneyType($moneyType)
            ->setAmount($amount)
            ->setAmountDontKnow($amountDontKnow)
            ->setWhoReceivedMoney($whoReceived);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount($expectedViolationCount, $result);
    }

    public function invalidDataProvider()
    {
        return [
            'Fails when $amountDontKnow is true and $moneyType, $amount and $whoReceived are null' => [
                null,
                null,
                true,
                null,
                3,
            ],
            'Fails when $moneyType is a non-empty string and $amount is null, $amountDontKnow is false and $whoReceived is null' => [
                'A type of income',
                null,
                false,
                null,
                2,
            ],
            'Fails when $amount is a number, $moneyType is null and $amountDontKnow is false' => [
                null,
                20,
                false,
                null,
                2,
            ],
            'Fails when $amount is a number, $moneyType is a non-empty string and $amountDontKnow is true' => [
                'Some income type',
                20,
                true,
                'Some org',
                1,
            ],
        ];
    }
}
