<?php

namespace App\Form;

use App\Form\Report\ReportType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ReportTypeTest extends TypeTestCase
{
    // ensures validation constraints are applied
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidYear()
    {
        $currentDate = new \DateTime();
        $currentYear = $currentDate->format('Y');
        $followingYear = $currentDate->modify('+1 year')->format('Y');

        $startDate = [
            'year' => $currentYear,
            'month' => '01',
            'day' => '02',
        ];

        $endDate = [
            'year' => $followingYear,
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

    public function testSubmitInvalidYear()
    {
        $startDate = [
            'year' => '2000',
            'month' => '01',
            'day' => '02',
        ];

        $endDate = [
            'year' => '2001',
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
        $errors = $form['startDate']->getErrors();

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());

        $this->assertCount(1, $errors);
        $this->assertSame('Please enter a valid start date.', $errors[0]->getMessage());
    }
}
