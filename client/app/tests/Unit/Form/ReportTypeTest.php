<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Form;

use OPG\Digideps\Frontend\Form\Report\ReportType;
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

    public function testSubmitValidYear(): void
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

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());
    }

    public function testSubmitInvalidYear(): void
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

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());

        self::assertCount(1, $errors);
        self::assertSame('Please enter a valid start date.', $errors[0]->getMessage());
    }
}
