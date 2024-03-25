<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\User;
use App\Tests\Behat\v2\Common\UserDetails;

trait SelfRegistrationTrait
{
    private string $invalidCaseNumberError = "The case number you provided does not match our records.\nPlease call 0115 934 2700 to make sure we have a record of your deputyship.";
    private string $invalidDeputyFirstnameError = 'Your first name you provided does not match our records.';
    private string $invalidDeputyLastnameError = 'Your last name you provided does not match our records.';
    private string $invalidDeputyPostcodeError = 'The postcode you provided does not match our records.';
    private string $invalidClientLastnameError = "The client's last name you provided does not match our records.";
    private string $deputyNotUniquelyIdentifiedError = "The information you've given us does not allow us to uniquely identify you as the deputy.\nPlease call 0115 934 2700 to make sure we have the correct record of your deputyship.";
    private string $userEmail;
    private string $coDeputyEmail;
    private string $deputyUid;

    /**
     * @Given a Lay Deputy registers to deputise for a client with valid details
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithValidDetails()
    {
        $this->userEmail = 'julie@duck.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
        $this->deputyUid = '19371937';

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Julie',
            'Duck',
            $this->userEmail,
            'B1',
            'Billy',
            'Huey',
            '31313131',
        );
        
        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->userEmail, 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $this->userEmail);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

        $this->fillClientDetailsAndSubmit();

        $this->fillInReportDetailsAndSubmit();
    }

    /**
     * @Given a Lay Deputy registers to deputise for a client with an invalid case number
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithAnInvalidCaseNumber()
    {
        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Brian',
            'Duck',
            'brian2@duck.co.uk',
            'B1',
            'Billy',
            'Huey',
            '31313137',
        );
    }

    /**
     * @Given a Lay Deputy registers to deputise for a client with a valid case number and invalid case details
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithAnValidCaseNumberAndInvalidCaseDetails()
    {
        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Wrong',
            'Name',
            'brian3@duck.co.uk',
            'ABC 123',
            'Wrong',
            'Name',
            '31313131',
        );
    }

    private function fillInSelfRegistrationFieldsAndSubmit(
        string $firstname,
        string $lastname,
        string $email,
        string $postcode,
        string $clientFirstname,
        string $clientLastname,
        string $caseNumber
    ) {
        $this->fillInField('self_registration_firstname', $firstname);
        $this->fillInField('self_registration_lastname', $lastname);
        $this->fillInField('self_registration_email_first', $email);
        $this->fillInField('self_registration_email_second', $email);
        $this->fillInField('self_registration_postcode', $postcode);
        $this->fillInField('self_registration_clientFirstname', $clientFirstname);
        $this->fillInField('self_registration_clientLastname', $clientLastname);
        $this->fillInField('self_registration_caseNumber', $caseNumber);
        $this->pressButton('self_registration_save');
    }

    /**
     * @Then I should see an 'invalid case number' error
     */
    public function iShouldSeeAnInvalidCaseNumberError()
    {
        $this->assertOnErrorMessage($this->invalidCaseNumberError);
    }

    /**
     * @Then I should see an 'invalid deputy firstname' error
     */
    public function iShouldSeeAnInvalidDeputyFirstnameError()
    {
        $this->assertOnErrorMessage($this->invalidDeputyFirstnameError);
    }
    
    /**
     * @Then I should see an 'invalid deputy lastname' error
     */
    public function iShouldSeeAnInvalidDeputyLastnameError()
    {
        $this->assertOnErrorMessage($this->invalidDeputyLastnameError);
    }

    /**
     * @Then I should see an 'invalid deputy postcode' error
     */
    public function iShouldSeeAnInvalidDeputyPostcodeError()
    {
        $this->assertOnErrorMessage($this->invalidDeputyPostcodeError);
    }

    /**
     * @Then I should see an 'invalid client lastname' error
     */
    public function iShouldSeeAnInvalidClientLastnameError()
    {
        $this->assertOnErrorMessage($this->invalidClientLastnameError);
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
        $this->deputyUid = '19371938';

        $this->setPasswordAndTickTAndCs();

        $this->pressButton('Submit');

        $this->fillField('login_email', $this->interactingWithUserDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

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

        $this->fillInReportDetailsAndSubmit();
    }

    /**
     * @Given /^I create a Lay Deputy user account for one of the deputies in the CSV$/
     */
    public function iCreateALayDeputyUserAccountForOneOfTheDeputysInTheCSV()
    {
        $this->iVisitAdminAddUserPage();
        $this->userEmail = 'VANDERQUACK@DUCKTAILS.com';

        $this->fillInField('admin_email', $this->userEmail);
        $this->fillInField('admin_firstname', 'Maria');
        $this->fillInField('admin_lastname', 'Vanderquack');
        $this->fillInField('admin_addressPostcode', 'SW1');
        $this->selectOption('admin[roleType]', 'deputy');
        $this->selectOption('admin[roleNameDeputy]', 'ROLE_LAY_DEPUTY');

        $this->pressButton('Save user');

        $this->assertOnAlertMessage('email has been sent to the user');

        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
    }

    /**
     * @Then my deputy details should be saved to my account
     */
    public function mySelfRegistrationDetailsShouldBeSavedToMyAccount()
    {
        $this->em->flush();
        $this->em->clear();

        /** @var User $deputy */
        $deputy = $this->em->getRepository(User::class)->findOneBy(
            ['email' => strtolower($this->interactingWithUserDetails->getUserEmail())]
        );

        $this->assertStringEqualsString($this->deputyUid, $deputy->getDeputyNo(), 'Asserting DeputyUid is the same');
        $this->assertStringEqualsString('102 Petty France', $deputy->getAddress1(), 'Asserting Address Line 1 is the same');
        $this->assertStringEqualsString('MOJ', $deputy->getAddress2(), 'Asserting Address Line 2 is the same');
        $this->assertStringEqualsString('London', $deputy->getAddress3(), 'Asserting Address Line 3 is the same');
        $this->assertStringEqualsString('GB', $deputy->getAddressCountry(), 'Asserting Address Country is the same');
        $this->assertStringEqualsString('01789 321234', $deputy->getPhoneMain(), 'Asserting Main Phone is the same');
    }

    /**
     * @Given one of the Lay Deputies registers to deputise for a client with valid details
     */
    public function oneOfTheLayDeputiesRegistersToDeputiseForAClientWithValidDetails()
    {
        $this->userEmail = 'brian@mcduck.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
        $this->deputyUid = '35672419';

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Brian',
            'McDuck',
            $this->userEmail,
            'B73',
            'Billy',
            'Louie',
            '1717171T',
        );

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->userEmail, 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $this->userEmail);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

        $this->fillClientDetailsAndSubmit();

        $this->fillInReportDetailsAndSubmit();
    }

    /**
     * @When I invite a Co-Deputy to the service
     */
    public function iInviteACoDeputyToTheService()
    {
        $matches = [];
        preg_match('/[^\/]+$/', $this->getCurrentUrl(), $matches);
        $clientId = $matches[0];

        $this->getCurrentUrl();
        $this->visitPath(sprintf('/codeputy/%s/add', $clientId));

        $this->coDeputyEmail = 'liam@mcquack.co.uk';

        $this->fillInField('co_deputy_invite_email', $this->coDeputyEmail);
        $this->pressButton('co_deputy_invite_submit');
    }

    /**
     * @Then /^they should be able to register to deputise for a client with valid details$/
     */
    public function theyShouldBeAbleToRegisterToDeputiseForAClientWithValidDetails()
    {
        $this->visitPath('/logout');
        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->coDeputyEmail, 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $this->coDeputyEmail);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('co_deputy_firstname', 'Liam');
        $this->fillInField('co_deputy_lastname', 'McQuack');
        $this->fillInField('co_deputy_address1', 'Fieldag');
        $this->fillInField('co_deputy_addressPostcode', 'Y73');
        $this->fillInField('co_deputy_addressCountry', 'GB');
        $this->fillInField('co_deputy_phoneMain', '01789432876');
        $this->fillInField('co_deputy_clientLastname', 'Louie');
        $this->fillInField('co_deputy_clientCaseNumber', '1717171T');

        $this->pressButton('co_deputy_save');
    }

    /**
     * @Given a Lay Deputy registers with valid details using unicode characters
     */
    public function aLayDeputyRegistersWithValidDetailsUsingUnicodeChars()
    {
        $this->userEmail = 'jeanne@darc.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
        $this->deputyUid = '15151515';

        $this->visitFrontendPath('/register');

        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Jeanne',
            'd\'Arc',
            $this->userEmail,
            'B1',
            'Test',
            'O’Name',
            '51515151',
        );

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->userEmail, 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $this->userEmail);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

        $this->fillClientDetailsAndSubmit();

        $this->fillInReportDetailsAndSubmit();
    }

    private function setPasswordAndTickTAndCs(): void
    {
        $this->fillInField('set_password_password_first', 'DigidepsPass1234');
        $this->fillInField('set_password_password_second', 'DigidepsPass1234');
        $this->checkOption('set_password_showTermsAndConditions');
    }

    private function fillUserDetailsAndSubmit(): void
    {
        $this->fillInField('user_details_address1', '102 Petty France');
        $this->fillInField('user_details_address2', 'MOJ');
        $this->fillInField('user_details_address3', 'London');
        $this->fillInField('user_details_addressCountry', 'GB');
        $this->fillInField('user_details_phoneMain', '01789 321234');
        $this->pressButton('user_details_save');
    }

    private function fillClientDetailsAndSubmit(): void
    {
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
    }

    private function fillInReportDetailsAndSubmit(): void
    {
        $this->fillInField('report_startDate_day', '01');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2016');
        $this->fillInField('report_endDate_day', '31');
        $this->fillInField('report_endDate_month', '12');
        $this->fillInField('report_endDate_year', '2016');
        $this->pressButton('report_save');
    }

    /**
     * @Given a Lay Deputy registers to deputise for a client with details that are not unique 
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithSimilarDetails(): void
    {
        $this->userEmail = 'julie@duck.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Julie',
            'Duck',
            $this->userEmail,
            'B1',
            'Billy',
            'Huey',
            '31313131',
        );
    }

    /**
     * @Then I should see a 'deputy not uniquely identified' error
     */
    public function iShouldSeeADeputyNotUniquelyIdentifiedError(): void
    {
        $this->assertOnErrorMessage($this->deputyNotUniquelyIdentifiedError);
    }
}
