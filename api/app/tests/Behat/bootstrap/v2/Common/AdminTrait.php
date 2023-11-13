<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait AdminTrait
{
    private string $missingFirstNameError = 'Enter the first name';
    private string $missingLastNameError = 'Enter the last name';
    private string $missingPostcodeError = 'Enter your postcode';
    private string $missingEmailError = 'Enter an email address';
    private string $invalidEmail = 'This email is not valid';
    private string $invalidPostcode = 'The postcode cannot be longer than 10 characters';

    /**
     * @When I enter the wrong type of values
     */
    public function iEnterWrongValueTypes()
    {
        $this->fillField('admin[firstname]', $this->faker->firstName());
        $this->fillField('admin[lastname]', $this->faker->lastName());
        $this->fillField('admin[addressPostcode]', $this->faker->sentence(5));
        $this->fillField('admin[email]', $this->faker->sentence(5));
        $this->pressButton('Update user');
    }

    /**
     * @Then I should see 'type validation' errors
     */
    public function iShouldSeeTypeValidationError()
    {
        $this->assertOnErrorMessage($this->invalidEmail);
        $this->assertOnErrorMessage($this->invalidPostcode);
    }

    /**
     * @When I enter empty values
     */
    public function iDoNotEnterValues()
    {
        $this->fillField('admin[firstname]', '');
        $this->fillField('admin[lastname]', '');
        $this->fillField('admin[addressPostcode]', '');
        $this->fillField('admin[email]', '');

        $this->pressButton('Update user');
    }

    /**
     * @Then I should see 'missing values' errors
     */
    public function iShouldSeeMissingValuesErrors()
    {
        $this->assertOnErrorMessage($this->missingFirstNameError);
        $this->assertOnErrorMessage($this->missingLastNameError);
        $this->assertOnErrorMessage($this->missingPostcodeError);
        $this->assertOnErrorMessage($this->missingEmailError);
    }
}
