<?php

declare(strict_types=1);

namespace Tests\App\Entity\Ndr;

use App\Entity\Ndr\ClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf;
use App\TestHelpers\NdrHelpers;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ClientBenefitsCheckTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider invalidDataProvider
     */
    public function validation(
        ?string $whenLastChecked,
        ?\DateTime $dateLastChecked,
        ?string $neverCheckedExplanation,
        ?string $doOthersReceiveMoney,
        ?string $moneyExplanation,
        ?ArrayCollection $moneyTypes,
        int $expectedValidationErrorsCount
    ) {
        $ndr = NdrHelpers::createNdr();

        $sut = (new ClientBenefitsCheck())
            ->setWhenLastCheckedEntitlement($whenLastChecked)
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($neverCheckedExplanation)
            ->setDoOthersReceiveMoneyOnClientsBehalf($doOthersReceiveMoney)
            ->setDontKnowMoneyExplanation($moneyExplanation)
            ->setTypesOfMoneyReceivedOnClientsBehalf($moneyTypes)
            ->setNdr($ndr);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $result = $validator->validate($sut, null, 'client-benefits-check');

        $this->assertCount($expectedValidationErrorsCount, $result);
    }

    public function invalidDataProvider()
    {
        $moneyType = (new MoneyReceivedOnClientsBehalf())
        ->setAmountDontKnow(false);

        $moneyTypes = new ArrayCollection();
        $moneyTypes->add($moneyType);

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
            "Fails when \$doOthersReceiveMoneyOnClientsBehalf is 'dontKnow' and \$dontKnowMoneyExplanation is null" => [
                'currentlyChecking',
                null,
                null,
                'dontKnow',
                null,
                null,
                1,
            ],
            'Fails when one money type exists in $typesOfMoneyReceivedOnClientsBehalf but money amount and type are null and amountDontKnow is false' => [
                'currentlyChecking',
                null,
                null,
                'yes',
                null,
                $moneyTypes,
                5,
            ],
        ];
    }
}
