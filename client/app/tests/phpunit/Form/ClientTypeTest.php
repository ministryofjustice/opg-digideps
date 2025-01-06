<?php

namespace App\Form;

use Symfony\Component\Form\Test\TypeTestCase;

class ClientTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $courtDate = [
            'year' => '2024',
            'month' => '01',
            'day' => '01',
        ];

        $formData = [
            'firstname' => 'Mel',
            'lastname' => 'Jones',
            'caseNumber' => '0000000',
            'courtDate' => $courtDate,
            'address' => '10 Downing Street',
            'phone' => '0123456789',
        ];

        $form = $this->factory->create(ClientType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isValid());
    }

    public function testSubmitInvalidYear()
    {
        $courtDate = [
            'year' => '20',
            'month' => '01',
            'day' => '01',
        ];

        $formData = [
            'firstname' => 'Mel',
            'lastname' => 'Jones',
            'caseNumber' => '0000000',
            'courtDate' => $courtDate,
            'address' => '10 Downing Street',
            'phone' => '0123456789',
        ];

        $form = $this->factory->create(ClientType::class);

        $form->submit($formData);
        $errors = $form->getErrors();

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());

        $this->assertCount(1, $errors);
        $this->assertSame('Please enter a valid four-digit year.', $errors[0]->getMessage());
    }
}
