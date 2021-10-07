<?php

declare(strict_types=1);

namespace App\Tests\Behat\App\Form\Report;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\Form\Report\IncomeReceivedOnClientsBehalfType;
use Symfony\Component\Form\Test\TypeTestCase;

class IncomeReceivedOnClientsBehalfTypeTest extends TypeTestCase
{
    /**
     * @test
     * @dataProvider formDataProvider
     */
    public function buildForm(string $incomeType, ?int $amount, ?bool $amountDontKnow)
    {
        $formData = [
            'incomeType' => $incomeType,
            'amount' => $amount,
            'amountDontKnow' => $amountDontKnow,
        ];

        $model = new IncomeReceivedOnClientsBehalf();

        $expected = (new IncomeReceivedOnClientsBehalf())
            ->setIncomeType($incomeType)
            ->setAmount($amount)
            ->setAmountDontKnow($amountDontKnow);

        $form = $this->factory->create(IncomeReceivedOnClientsBehalfType::class, $model);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }

    public function formDataProvider()
    {
        return [
            'Income type and amount' => ['Fake type', 5, null],
            'Income type and dont know amount' => ['Fake type', null, true],
        ];
    }

    /**
     * @test
     */
    public function buildFormSettingDontKnowAmountToTrueNullsAmount()
    {
        $formData = [
            'incomeType' => 'Fake type',
            'amount' => 5,
            'amountDontKnow' => true,
        ];

        $model = new IncomeReceivedOnClientsBehalf();

        $expected = (new IncomeReceivedOnClientsBehalf())
            ->setIncomeType('Fake type')
            ->setAmount(null)
            ->setAmountDontKnow(true);

        $form = $this->factory->create(IncomeReceivedOnClientsBehalfType::class, $model);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
