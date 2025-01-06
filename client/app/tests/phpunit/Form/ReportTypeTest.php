<?php

namespace App\Form;

use App\Form\Report\ReportType;
use Symfony\Component\Form\Test\TypeTestCase;

class ReportTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $startDate = [
            'year' => '2022',
            'month' => '01',
            'day' => '02',
        ];

        $endDate = [
            'year' => '2023',
            'month' => '01',
            'day' => '02',
        ];

        $formData = [
            'id' => 1,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $form = $this->factory->create(ReportType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    public function testSubmitInvalidData()
    {
        $startDate = [
            'year' => '22',
            'month' => '01',
            'day' => '02',
        ];

        $endDate = [
            'year' => '23',
            'month' => '01',
            'day' => '02',
        ];

        $formData = [
            'id' => 1,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $form = $this->factory->create(ReportType::class);

        $form->submit($formData);
        $errors = $form->getErrors();

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());

        $this->assertCount(1, $errors);
        $this->assertSame('Please enter a valid four-digit year.', $errors[0]->getMessage());
    }
}
