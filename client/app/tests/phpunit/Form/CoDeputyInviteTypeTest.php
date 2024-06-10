<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class CoDeputyInviteTypeTest extends TypeTestCase
{
    // Integrates the validator into the form factory which ensures that the validation
    // constraints set up in the entity annotations are applied during form validation
    protected function getExtensions()
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmissionWithValidData()
    {
        $formData = [
            'firstname' => 'Sarah',
            'lastname' => 'Jones',
            'email' => 'sarah@hotmail.co.uk',
        ];

        $invitedUser = new User();
        $form = $this->factory->create(CoDeputyInviteType::class, $invitedUser);

        $expectedInvitedUser = new User();
        $expectedInvitedUser->setFirstname('Sarah');
        $expectedInvitedUser->setLastname('Jones');
        $expectedInvitedUser->setEmail('sarah@hotmail.co.uk');

        $form->submit($formData);

        $this->assertTrue($form->isValid());
        $this->assertEquals($expectedInvitedUser, $invitedUser);
    }

    public function testSubmissionWithMissingMandatoryData()
    {
        $formData = [
            'firstname' => '',
            'lastname' => 'Jones',
            'email' => 'sarah@hotmail.co.uk',
        ];

        $invitedUser = new User();
        $form = $this->factory->create(CoDeputyInviteType::class, $invitedUser);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $errors = $form['firstname']->getErrors();
        $this->assertGreaterThan(0, $errors->count());
    }

    public function testSubmissionWhenMinLengthIsNotMet()
    {
        $formData = [
            'firstname' => 'Jamie',
            'lastname' => 'J',
            'email' => 'jamie@hotmail.co.uk',
        ];

        $invitedUser = new User();
        $form = $this->factory->create(CoDeputyInviteType::class, $invitedUser);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $errors = $form['lastname']->getErrors();
        $this->assertGreaterThan(0, $errors->count());
    }
}
