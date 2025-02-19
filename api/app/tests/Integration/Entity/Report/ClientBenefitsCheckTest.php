<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Report;

use App\Entity\Report\ClientBenefitsCheck;
use PHPUnit\Framework\TestCase;

class ClientBenefitsCheckTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider whenCheckedProvider
     */
    public function setWhenLastCheckedEntitlementSetsNeverCheckedNullWhenValueIsNotNeverChecked(
        string $whenChecked,
        ?string $expectedExplanation,
    ) {
        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED)
            ->setNeverCheckedExplanation('An explanation');

        $sut->setWhenLastCheckedEntitlement($whenChecked);

        self::assertEquals($expectedExplanation, $sut->getNeverCheckedExplanation());
    }

    public function whenCheckedProvider()
    {
        return [
            'haveChecked' => [ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED, null],
            'currentlyChecking' => [ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING, null],
            'neverChecked' => [ClientBenefitsCheck::WHEN_CHECKED_IVE_NEVER_CHECKED, 'An explanation'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider otherMoneyProvider
     */
    public function setDoOthersReceiveMoneyOnClientsBehalfSetsDontKnowNullWhenValueIsNotDontKnow(
        string $otherMoneyReceived,
        ?string $expectedExplanation,
    ) {
        $sut = (new ClientBenefitsCheck())
            ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW)
            ->setDontKnowMoneyExplanation('Another explanation');

        $sut->setDoOthersReceiveMoneyOnClientsBehalf($otherMoneyReceived);

        self::assertEquals($expectedExplanation, $sut->getDontKnowMoneyExplanation());
    }

    public function otherMoneyProvider()
    {
        return [
            'no' => [ClientBenefitsCheck::OTHER_MONEY_NO, null],
            'yes' => [ClientBenefitsCheck::OTHER_MONEY_YES, null],
            'dontKnow' => [ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW, 'Another explanation'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider dateCheckedProvider
     */
    public function setWhenLastCheckedEntitlementSetsDateNullWhenValueIsNotHaveChecked(
        string $whenChecked,
        ClientBenefitsCheck $sut,
        ?\DateTime $expectedDateLastChecked,
    ) {
        $sut->setWhenLastCheckedEntitlement($whenChecked);

        self::assertEquals($expectedDateLastChecked, $sut->getDateLastCheckedEntitlement());
    }

    public function dateCheckedProvider()
    {
        $now = new \DateTime();
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
