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
     * @dataProvider invalidDataProvider
     */
    public function testValidation($moneyType, $amount, $amountDontKnow)
    {
        $sut = (new MoneyReceivedOnClientsBehalf())
            ->setMoneyType($moneyType)
            ->setAmount($amount)
            ->setAmountDontKnow($amountDontKnow);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount(1, $result);
    }

    public function invalidDataProvider()
    {
        return [
            'Fails when $amountDontKnow is true and $moneyType and $amount is null' => [
                null,
                null,
                true,
            ],
            'Fails when $moneyType is a non-empty string and $amount is null and $amountDontKnow is false' => [
                'A type of money',
                null,
                false,
            ],
            'Fails when $amount is a number, $moneyType is null and $amountDontKnow is false' => [
                null,
                20,
                false,
            ],
            'Fails when $amount is a number, $moneyType is a non-empty string and $amountDontKnow is true' => [
                'Some money type',
                20,
                true,
            ],
        ];
    }
}
