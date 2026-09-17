<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Form;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Form\CoDeputyInviteType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class CoDeputyInviteTypeTest extends TypeTestCase
{
    // Integrates the validator into the form factory which ensures that the validation
    // constraints set up in the entity annotations are applied during form validation
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmissionWithValidData(): void
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

        self::assertTrue($form->isValid());
        self::assertEquals($expectedInvitedUser, $invitedUser);
    }

    public function testSubmissionWithMissingMandatoryData(): void
    {
        $formData = [
            'firstname' => '',
            'lastname' => 'Jones',
            'email' => 'sarah@hotmail.co.uk',
        ];

        $invitedUser = new User();
        $form = $this->factory->create(CoDeputyInviteType::class, $invitedUser);
        $form->submit($formData);

        self::assertFalse($form->isValid());
        $errors = $form['firstname']->getErrors();
        self::assertGreaterThan(0, $errors->count());
    }

    public function testSubmissionWhenMinLengthIsNotMet(): void
    {
        $formData = [
            'firstname' => 'Jamie',
            'lastname' => 'J',
            'email' => 'jamie@hotmail.co.uk',
        ];

        $invitedUser = new User();
        $form = $this->factory->create(CoDeputyInviteType::class, $invitedUser);
        $form->submit($formData);

        self::assertFalse($form->isValid());
        $errors = $form['lastname']->getErrors();
        self::assertGreaterThan(0, $errors->count());
    }
}
