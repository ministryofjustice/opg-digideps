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
        ?ArrayCollection $incomeTypes
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

        $this->assertCount(1, $result);
    }

    public function invalidDataProvider()
    {
        $incomeType = (new IncomeReceivedOnClientsBehalf())
            ->setAmount(123.00)
            ->setIncomeType('Care fees');

        $incomeTypes = new ArrayCollection();
        $incomeTypes->add($incomeType);

        return [
//            "Fails when \$whenLastCheckedEntitlement is 'haveChecked' and \$dateLastCheckedEntitlement is null" => [
//                'haveChecked',
//                null,
//                null,
//                'no',
//                null,
//                null,
//            ],
//            "Fails when \$whenLastCheckedEntitlement is 'neverChecked' and \$neverCheckedExplanation is null" => [
//                'neverChecked',
//                null,
//                null,
//                'no',
//                null,
//                null,
//            ],
//            "Fails when \$doOthersReceiveIncomeOnClientsBehalf is 'dontKnow' and \$dontKnowIncomeExplanation is null" => [
//                'currentlyChecking',
//                null,
//                null,
//                'dontKnow',
//                null,
//                null,
//            ],
            "Fails when \$doOthersReceiveIncomeOnClientsBehalf is 'yes and \$typesOfIncomeReceivedOnClientsBehalf is null" => [
                'currentlyChecking',
                null,
                null,
                'yes',
                null,
                null,
            ],
        ];
    }
}
