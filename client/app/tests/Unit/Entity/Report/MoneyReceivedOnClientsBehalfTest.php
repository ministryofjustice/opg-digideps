<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\MoneyReceivedOnClientsBehalf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class MoneyReceivedOnClientsBehalfTest extends TestCase
{
    #[DataProvider('invalidDataProvider')]
    public function testValidation(?string $moneyType, ?int $amount, bool $amountDontKnow, ?string $whoReceived, int $expectedViolationCount)
    {
        $sut = new MoneyReceivedOnClientsBehalf()
            ->setMoneyType($moneyType)
            ->setAmount($amount)
            ->setAmountDontKnow($amountDontKnow)
            ->setWhoReceivedMoney($whoReceived);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        self::assertCount($expectedViolationCount, $result);
    }

    public function testTextFieldsCannotExceed255Characters(): void
    {
        $sut = new MoneyReceivedOnClientsBehalf()
            ->setMoneyType(str_repeat('a', 256))
            ->setAmount(20)
            ->setWhoReceivedMoney(str_repeat('b', 256));

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        self::assertCount(2, $result);
        self::assertSame(['moneyType', 'whoReceivedMoney'], array_map(
            static fn ($violation) => $violation->getPropertyPath(),
            iterator_to_array($result)
        ));
    }

    public function testTextFieldsAllow255Characters(): void
    {
        $sut = new MoneyReceivedOnClientsBehalf()
            ->setMoneyType(str_repeat('a', 255))
            ->setAmount(20)
            ->setWhoReceivedMoney(str_repeat('b', 255));

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        self::assertCount(0, $result);
    }

    public static function invalidDataProvider(): array
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
