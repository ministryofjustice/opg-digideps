<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Tests\Behat\v2\Common\UserDetails;

trait SelfRegistrationTrait
{
    /**
     * @Given a Lay Deputy registers to deputise for a client with valid details
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithValidDetails()
    {
        $this->visitFrontendPath('/register');
        $this->fillInField('self_registration_firstname', 'Brian');
        $this->fillInField('self_registration_lastname', 'Duck');
        $this->fillInField('self_registration_email_first', 'brian@duck.co.uk');
        $this->fillInField('self_registration_email_second', 'brian@duck.co.uk');
        $this->fillInField('self_registration_postcode', 'B1');
        $this->fillInField('self_registration_clientFirstname', 'Billy');
        $this->fillInField('self_registration_clientLastname', 'Huey');
        $this->fillInField('self_registration_caseNumber', '31313131');
        $this->pressButton('self_registration_save');

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', 'brian@duck.co.uk');
        $this->fillInField('set_password_password_first', 'DigidepsPass1234');
        $this->fillInField('set_password_password_second', 'DigidepsPass1234');
        $this->checkOption('set_password_showTermsAndConditions');
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', 'brian@duck.co.uk');
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('user_details_address1', '102 Petty France');
        $this->fillInField('user_details_address2', 'MOJ');
        $this->fillInField('user_details_address3', 'London');
        $this->fillInField('user_details_addressCountry', 'GB');
        $this->fillInField('user_details_phoneMain', '01789 321234');
        $this->pressButton('user_details_save');

        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_address2', 'First Floor');
        $this->fillInField('client_address3', 'Big Building');
        $this->fillInField('client_address4', 'Large Town');
        $this->fillInField('client_address5', 'Notts');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_phone', '01789432876');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2016');
        $this->pressButton('client_save');

        $this->fillInField('report_startDate_day', '01');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2016');
        $this->fillInField('report_endDate_day', '31');
        $this->fillInField('report_endDate_month', '12');
        $this->fillInField('report_endDate_year', '2016');
        $this->pressButton('report_save');
    }

    /**
     * @Given /^a Lay Deputy clicks the activation link in the registration email$/
     */
    public function aLayDeputyClicksTheActivationLinkInTheRegistrationEmail()
    {
        $this->iVisitTheActivateUserPageInteractingUser();
    }

    /**
     * @Given /^I complete the case manager user registration flow with valid deputyship details$/
     */
    public function iCompleteTheCaseManagerUserRegistrationFlowWithValidDeputyhsipDetails()
    {
        $this->fillInField('set_password_password_first', 'DigidepsPass1234');
        $this->fillInField('set_password_password_second', 'DigidepsPass1234');
        $this->checkOption('set_password_showTermsAndConditions');

        $this->pressButton('Submit');

        $this->fillField('login_email', $this->interactingWithUserDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('user_details_address1', '102 Petty France');
        $this->fillInField('user_details_address2', 'MOJ');
        $this->fillInField('user_details_address3', 'London');
        $this->fillInField('user_details_addressCountry', 'GB');
        $this->fillInField('user_details_phoneMain', '01789 321234');
        $this->pressButton('user_details_save');

        $this->fillInField('client_firstname', $this->faker->firstName());
        $this->fillInField('client_lastname', 'DEWEY');
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_address2', 'First Floor');
        $this->fillInField('client_address3', 'Big Building');
        $this->fillInField('client_address4', 'Large Town');
        $this->fillInField('client_address5', 'Notts');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_phone', '01789432876');
        $this->fillInField('client_caseNumber', '32323232');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2016');
        $this->pressButton('client_save');

        $this->fillInField('report_startDate_day', '01');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2016');
        $this->fillInField('report_endDate_day', '31');
        $this->fillInField('report_endDate_month', '12');
        $this->fillInField('report_endDate_year', '2016');
        $this->pressButton('report_save');

//        $this->fillInField('admin[email]', $userEmail);
    }

    /**
     * @Given /^I create a Lay Deputy user account for one of the deputies in the CSV$/
     */
    public function iCreateALayDeputyUserAccountForOneOfTheDeputysInTheCSV()
    {
        $this->iVisitAdminAddUserPage();
        $userEmail = 'VANDERQUACK@DUCKTAILS.com';

        $this->fillInField('admin_email', $userEmail);
        $this->fillInField('admin_firstname', $this->faker->firstName);
        $this->fillInField('admin_lastname', 'Vanderquack');
        $this->fillInField('admin_addressPostcode', 'SW1');
        $this->selectOption('admin[roleType]', 'deputy');
        $this->selectOption('admin[roleNameDeputy]', 'ROLE_LAY_DEPUTY');

        $this->pressButton('Save user');

        $this->assertOnAlertMessage('email has been sent to the user');

        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $userEmail]);
    }
}
