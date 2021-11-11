<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ClientBenefitsCheckTest extends TestCase
{
    /**
     * @test
     * @dataProvider invalidDataProvider
     */
    public function validation(
        ?string $whenLastChecked,
        ?DateTime $dateLastChecked,
        ?string $neverCheckedExplanation,
        ?string $doOthersReceiveIncome,
        ?string $incomeExplanation,
        ?ArrayCollection $incomeTypes,
        int $expectedValidationErrorsCount
    ) {
        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement($whenLastChecked)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($neverCheckedExplanation)
            ->setDoOthersReceiveIncomeOnClientsBehalf($doOthersReceiveIncome)
            ->setDontKnowIncomeExplanation($incomeExplanation)
            ->setTypesOfIncomeReceivedOnClientsBehalf($incomeTypes);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount($expectedValidationErrorsCount, $result);
    }

    public function invalidDataProvider()
    {
        $incomeType = (new IncomeReceivedOnClientsBehalf())
        ->setAmountDontKnow(false);

        $incomeTypes = new ArrayCollection();
        $incomeTypes->add($incomeType);

        return [
            "Fails when \$whenLastCheckedEntitlement is 'haveChecked' and \$dateLastCheckedEntitlement is null" => [
                'haveChecked',
                null,
                null,
                'no',
                null,
                null,
                1,
            ],
            "Fails when \$whenLastCheckedEntitlement is 'neverChecked' and \$neverCheckedExplanation is null" => [
                'neverChecked',
                null,
                null,
                'no',
                null,
                null,
                1,
            ],
            "Fails when \$doOthersReceiveIncomeOnClientsBehalf is 'dontKnow' and \$dontKnowIncomeExplanation is null" => [
                'currentlyChecking',
                null,
                null,
                'dontKnow',
                null,
                null,
                1,
            ],
            'Fails when one income type exists in $typesOfIncomeReceivedOnClientsBehalf but income amount and type are null and amountDontKnow is false' => [
                'currentlyChecking',
                null,
                null,
                'yes',
                null,
                $incomeTypes,
                2,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider whenCheckedProvider
     */
    public function setWhenLastCheckedEntitlementSetsNeverCheckedNullWhenValueIsNotNeverChecked(
        string $whenChecked,
        ?string $expectedExplanation
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
     * @dataProvider otherIncomeProvider
     */
    public function setDoOthersReceiveIncomeOnClientsBehalfSetsDontKnowNullWhenValueIsNotDontKnow(
        string $otherIncomeReceived,
        ?string $expectedExplanation
    ) {
        $sut = (new ClientBenefitsCheck())
            ->setDoOthersReceiveIncomeOnClientsBehalf(ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW)
            ->setDontKnowIncomeExplanation('Another explanation');

        $sut->setDoOthersReceiveIncomeOnClientsBehalf($otherIncomeReceived);

        self::assertEquals($expectedExplanation, $sut->getDontKnowIncomeExplanation());
    }

    public function otherIncomeProvider()
    {
        return [
            'no' => [ClientBenefitsCheck::OTHER_INCOME_NO, null],
            'yes' => [ClientBenefitsCheck::OTHER_INCOME_YES, null],
            'dontKnow' => [ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW, 'Another explanation'],
        ];
    }

    /**
     * @test
     * @dataProvider dateCheckedProvider
     */
    public function setWhenLastCheckedEntitlementSetsDateNullWhenValueIsNotHaveChecked(
        string $whenChecked,
        ClientBenefitsCheck $sut,
        ?DateTime $expectedDateLastChecked
    ) {
        $sut->setWhenLastCheckedEntitlement($whenChecked);

        self::assertEquals($expectedDateLastChecked, $sut->getDateLastCheckedEntitlement());
    }

    public function dateCheckedProvider()
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
