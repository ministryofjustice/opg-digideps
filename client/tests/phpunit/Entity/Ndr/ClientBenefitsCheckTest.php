<?php

declare(strict_types=1);

namespace Tests\App\Entity\Ndr;

use App\Entity\Ndr\ClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf;
use App\TestHelpers\NdrHelpers;
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
        $ndr = NdrHelpers::createNdr();

        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement($whenLastChecked)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($neverCheckedExplanation)
            ->setDoOthersReceiveIncomeOnClientsBehalf($doOthersReceiveIncome)
            ->setDontKnowIncomeExplanation($incomeExplanation)
            ->setTypesOfIncomeReceivedOnClientsBehalf($incomeTypes)
            ->setNdr($ndr);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount($expectedValidationErrorsCount, $result);
    }

    public function invalidDataProvider()
    {
        $incomeType = (new MoneyReceivedOnClientsBehalf())
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
}
