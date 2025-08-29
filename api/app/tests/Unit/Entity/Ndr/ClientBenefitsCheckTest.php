<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Ndr;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use DateTime;
use App\Entity\Ndr\ClientBenefitsCheck;
use PHPUnit\Framework\TestCase;

final class ClientBenefitsCheckTest extends TestCase
{
    #[DataProvider('whenCheckedProvider')]
    #[Test]
    public function setWhenLastCheckedEntitlementSetsNeverCheckedNullWhenValueIsNotNeverChecked(
        string $whenChecked,
        ?string $expectedExplanation
    ): void {
        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED)
            ->setNeverCheckedExplanation('An explanation');

        $sut->setWhenLastCheckedEntitlement($whenChecked);

        self::assertEquals($expectedExplanation, $sut->getNeverCheckedExplanation());
    }

    public static function whenCheckedProvider(): array
    {
        return [
            'haveChecked' => [ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED, null],
            'currentlyChecking' => [ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING, null],
            'neverChecked' => [ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED, 'An explanation'],
        ];
    }


    #[DataProvider('otherMoneyProvider')]
    #[Test]
    public function setDoOthersReceiveMoneyOnClientsBehalfSetsDontKnowNullWhenValueIsNotDontKnow(
        string $otherMoneyReceived,
        ?string $expectedExplanation
    ): void {
        $sut = (new ClientBenefitsCheck())
            ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW)
            ->setDontKnowMoneyExplanation('Another explanation');

        $sut->setDoOthersReceiveMoneyOnClientsBehalf($otherMoneyReceived);

        self::assertEquals($expectedExplanation, $sut->getDontKnowMoneyExplanation());
    }

    public static function otherMoneyProvider(): array
    {
        return [
            'no' => [ClientBenefitsCheck::OTHER_MONEY_NO, null],
            'yes' => [ClientBenefitsCheck::OTHER_MONEY_YES, null],
            'dontKnow' => [ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW, 'Another explanation'],
        ];
    }


    #[DataProvider('dateCheckedProvider')]
    #[Test]
    public function setWhenLastCheckedEntitlementSetsDateNullWhenValueIsNotHaveChecked(
        string $whenChecked,
        ClientBenefitsCheck $sut,
        ?DateTime $expectedDateLastChecked
    ): void {
        $sut->setWhenLastCheckedEntitlement($whenChecked);

        self::assertEquals($expectedDateLastChecked, $sut->getDateLastCheckedEntitlement());
    }

    public static function dateCheckedProvider(): array
    {
        $now = new DateTime();
        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
            ->setDateLastCheckedEntitlement($now);

        return [
            'haveChecked' => [ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED, $sut, $now],
            'currentlyChecking' => [ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING, $sut, null],
            'neverChecked' => [ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED, $sut, null],
        ];
    }
}
