<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\Client;
use App\Entity\User;
use App\Tests\Behat\v2\Common\UserDetails;
use Symfony\Component\HttpFoundation\Exception\JsonException;

trait SelfRegistrationTrait
{
    private string $invalidCaseNumberError = "The case number you provided does not match our records.\nPlease call 0300 456 0300 to make sure we have a record of your deputyship.";
    private string $invalidDeputyFirstnameError = "The deputy's first name you provided does not match our records.";
    private string $invalidDeputyLastnameError = "The deputy's last name you provided does not match our records.";
    private string $invalidDeputyPostcodeError = 'The postcode you provided does not match our records.';
    private string $invalidClientLastnameError = "The client's last name you provided does not match our records.";
    private string $incorrectCaseNumberLengthError = "The case number should be 8 or 10 characters long.\nPlease check your case reference number and try again.\n";
    private string $deputyNotUniquelyIdentifiedError = "The information you've given us does not allow us to uniquely identify you as the deputy.\nPlease call 0300 456 0300 to make sure we have the correct record of your deputyship.";
    private string $deputyAlreadyLinkedToCaseNumberError = 'You are already registered as a deputy for this case. Please check your case number and try again. If you have any questions, call our helpline on 0300 456 0300.';
    private string $reportingPeriodGreaterThanFifteenMonths = 'Check the end date: your reporting period cannot be more than 15 months';
    private string $userEmail;
    private string $coDeputyEmail;
    private string $deputyUid;
    private string $coDeputyUid;

    private array $cachedFixtures = [];

    private function getFixtureJson(string $jsonFile)
    {
        if (array_key_exists($jsonFile, $this->cachedFixtures)) {
            return $this->cachedFixtures[$jsonFile];
        }

        $file = file_get_contents(__DIR__.'/../../../fixtures/'.$jsonFile);

        $out = json_decode($file, true);
        if (is_null($out)) {
            throw new JsonException("Unable to parse JSON from file {$jsonFile}");
        }

        $this->cachedFixtures[$jsonFile] = $out;

        return $out;
    }

    /**
     * @Given the lay deputy :name @ :jsonFile registers as a deputy
     *
     * e.g. 'Given the lay deputy "Marbo Vantz" @ "ingest.lay.multiclient.sirius.json" registers as a deputy'
     *
     * This looks up a deputy in a specific json fixture file :jsonFile, using :name as a key into the JSON,
     * and registers them through the frontend. See the file referenced above for an example of the JSON format.
     */
    public function aLayDeputyWithRefRegistersToDeputise(string $jsonFile, string $name)
    {
        $fixture = $this->getFixtureJson($jsonFile);
        $regDetails = $fixture[$name];

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            $regDetails['deputy']['firstName'],
            $regDetails['deputy']['lastName'],
            $regDetails['deputy']['email'],
            $regDetails['deputy']['postcode'],
            $regDetails['client']['firstName'],
            $regDetails['client']['lastName'],
            $regDetails['caseNumber'],
        );

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $regDetails['deputy']['email'], 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->loginToFrontendAs($regDetails['deputy']['email']);

        $this->fillInField('user_details_address1', $regDetails['deputy']['address1']);
        $this->fillInField('user_details_addressCountry', $regDetails['deputy']['country']);
        $this->fillInField('user_details_phoneMain', $regDetails['deputy']['phone']);
        $this->pressButton('user_details_save');

        $this->fillInField('client_address', $regDetails['client']['address1']);
        $this->fillInField('client_postcode', $regDetails['client']['postcode']);
        $this->fillInField('client_country', $regDetails['client']['country']);
        $this->fillInField('client_phone', $regDetails['client']['phone']);
        $this->fillInField('client_courtDate_day', $regDetails['client']['courtDateDay']);
        $this->fillInField('client_courtDate_month', $regDetails['client']['courtDateMonth']);
        $this->fillInField('client_courtDate_year', $regDetails['client']['courtDateYear']);
        $this->pressButton('client_save');

        $this->fillInField('report_startDate_day', $regDetails['report']['startDay']);
        $this->fillInField('report_startDate_month', $regDetails['report']['startMonth']);
        $this->fillInField('report_startDate_year', $regDetails['report']['startYear']);
        $this->fillInField('report_endDate_day', $regDetails['report']['endDay']);
        $this->fillInField('report_endDate_month', $regDetails['report']['endMonth']);
        $this->fillInField('report_endDate_year', $regDetails['report']['endYear']);
        $this->pressButton('report_save');

        $this->visitFrontendPath('/logout');
    }

    /**
     * @Given a lay deputy :name @ :jsonFile is invited to be a co-deputy for case :caseNumber
     *
     * See aLayDeputyWithRefRegistersToDeputise for an explanation of the reference and JSON format.
     *
     * NB a user on the case referenced must be logged in for this sequence to work.
     */
    public function aLayDeputyIsInvitedToBeACodeputy(string $name, string $jsonFile, string $caseNumber)
    {
        $fixture = $this->getFixtureJson($jsonFile);
        $codeputy = $fixture[$name]['codeputy'];

        $clientId = $this->getClientIdByCaseNumber($caseNumber);
        $this->visitPath(sprintf('/codeputy/%s/add', $clientId));

        $this->fillInField('co_deputy_invite_firstname', $codeputy['firstName']);
        $this->fillInField('co_deputy_invite_lastname', $codeputy['lastName']);
        $this->fillInField('co_deputy_invite_email', $codeputy['email']);
        $this->pressButton('co_deputy_invite_submit');
    }

    /**
     * @When a lay deputy :name @ :jsonFile completes their registration as a co-deputy for case :caseNumber
     */
    public function aLayDeputyCompletesTheirRegistration(string $name, string $jsonFile, string $caseNumberIn)
    {
        $fixture = $this->getFixtureJson($jsonFile);
        $regDetails = $fixture[$name];
        $caseNumber = $regDetails['caseNumber'];

        $this->assertStringEqualsString($caseNumberIn, $regDetails['caseNumber'], 'caseNumber');

        $codeputy = $regDetails['codeputy'];
        $client = $regDetails['client'];

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $codeputy['email'], 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $codeputy['email']);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('co_deputy_firstname', $codeputy['firstName']);
        $this->fillInField('co_deputy_lastname', $codeputy['lastName']);
        $this->fillInField('co_deputy_address1', $codeputy['address1']);
        $this->fillInField('co_deputy_addressPostcode', $codeputy['postcode']);
        $this->fillInField('co_deputy_addressCountry', $codeputy['country']);
        $this->fillInField('co_deputy_phoneMain', $codeputy['phone']);
        $this->fillInField('co_deputy_clientLastname', $client['lastName']);
        $this->fillInField('co_deputy_clientCaseNumber', $caseNumber);

        $this->pressButton('co_deputy_save');
    }

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
        string $caseNumber,
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
        $actualErrorMessage = $this->getSession()->getPage()->find('css', 'ul.govuk-list.govuk-error-summary__list')->getHtml();
        $actualErrorMessageStripped = strip_tags($actualErrorMessage);

        $this->assertStringEqualsString($actualErrorMessageStripped, $this->invalidCaseNumberError, 'invalid case number error thrown');
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
     * @Then /^an incorrect case number length error is \'([^\']*)\'$/
     */
    public function anIncorrectCaseNumberLengthErrorIs($arg1)
    {
        if ('not thrown' == $arg1) {
            $this->assertPageContainsText(sprintf("We've sent you a link to %s that you need to click to activate your deputy service account.", $this->userEmail));
        } else {
            $actualErrorMessage = $this->getSession()->getPage()->find('css', 'ul.govuk-list.govuk-error-summary__list')->getHtml();
            $actualErrorMessageStripped = strip_tags($actualErrorMessage);

            $this->assertStringEqualsString($actualErrorMessageStripped, $this->incorrectCaseNumberLengthError, 'incorrect case number length error thrown');
        }
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
        $this->deputyUid = '19355556';

        $this->setPasswordAndTickTAndCs();

        $this->pressButton('Submit');

        $this->fillField('login_email', $this->interactingWithUserDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

        $this->fillInField('client_firstname', $this->faker->firstName());
        $this->fillInField('client_lastname', 'TUDOR');
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_address2', 'First Floor');
        $this->fillInField('client_address3', 'Big Building');
        $this->fillInField('client_address4', 'Large Town');
        $this->fillInField('client_address5', 'Notts');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_phone', '01789432876');
        $this->fillInField('client_caseNumber', '70777772');
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
        $this->fillInField('admin_firstname', 'Stuart');
        $this->fillInField('admin_lastname', 'Trump');
        $this->fillInField('admin_addressPostcode', 'M1');
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
        /* Assertion on the new Deputy UID value which is an exact match of the Deputy No value */
        $this->assertIntEqualsInt(intval($this->deputyUid), $deputy->getDeputyUid(), 'Asserting DeputyUid is the same');
        $this->assertStringEqualsString('102 Petty France', $deputy->getAddress1(), 'Asserting Address Line 1 is the same');
        $this->assertStringEqualsString('MOJ', $deputy->getAddress2(), 'Asserting Address Line 2 is the same');
        $this->assertStringEqualsString('London', $deputy->getAddress3(), 'Asserting Address Line 3 is the same');
        $this->assertStringEqualsString('GB', $deputy->getAddressCountry(), 'Asserting Address Country is the same');
        $this->assertStringEqualsString('01789 321234', $deputy->getPhoneMain(), 'Asserting Main Phone is the same');
    }

    /**
     * @Given one of the Lay Deputies registers to deputise for a client with valid details
     * @Given /^the same Lay deputy registers to deputise for a client with valid details$/
     */
    public function oneOfTheLayDeputiesRegistersToDeputiseForAClientWithValidDetails()
    {
        $this->userEmail = 'brian@mcduck.co.uk';
        $firstName = 'Brian';
        $lastName = 'McDuck';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail, 'deputyName' => $firstName.$lastName]);
        $this->deputyUid = '35672419';

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            $firstName,
            $lastName,
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

        $coDeputyFirstName = 'Liam';
        $coDeputyLastName = 'Mcquack';
        $this->coDeputyEmail = 'liam@mcquack.co.uk';

        $this->fillInField('co_deputy_invite_firstname', $coDeputyFirstName);
        $this->fillInField('co_deputy_invite_lastname', $coDeputyLastName);
        $this->fillInField('co_deputy_invite_email', $this->coDeputyEmail);
        $this->pressButton('co_deputy_invite_submit');
    }

    /**
     * @Given /^they register to deputise for a client with valid details that includes a (\d+) digit case number$/
     */
    public function theyRegisterToDeputiseForAClientWithValidDetailsThatIncludesADigitCaseNumber($caseNumLength)
    {
        if (8 == $caseNumLength) {
            $caseNumber = '1717171T';
        } else {
            $caseNumber = '1717171T00';
        }

        $this->coDeputyUid = '85462817';

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
        $this->fillInField('co_deputy_clientCaseNumber', $caseNumber);

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
        $this->fillInField('client_courtDate_day', '16');
        $this->fillInField('client_courtDate_month', '04');
        $this->fillInField('client_courtDate_year', '2023');
        $this->pressButton('client_save');
    }

    private function fillInReportDetailsAndSubmit(): void
    {
        $this->fillInField('report_startDate_day', '02');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2023');
        $this->fillInField('report_endDate_day', '01');
        $this->fillInField('report_endDate_month', '01');
        $this->fillInField('report_endDate_year', '2024');
        $this->pressButton('report_save');
    }

    /**
     * @Given a Lay Deputy registers to deputise for a client with details that are not unique
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithSimilarDetails(): void
    {
        $this->userEmail = 'julie1@duck.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Julie',
            'Duck',
            $this->userEmail,
            'B1',
            'Billy',
            'Huey',
            '31313135',
        );
    }

    /**
     * @Then I should see a 'deputy not uniquely identified' error
     */
    public function iShouldSeeADeputyNotUniquelyIdentifiedError(): void
    {
        $this->assertOnErrorMessage($this->deputyNotUniquelyIdentifiedError);
    }

    /**
     * @Then I/they should see a 'deputy already linked to case number' error
     */
    public function iShouldSeeADeputyAlreadyLinkedToCaseNumberError(): void
    {
        $this->assertOnErrorMessage($this->deputyAlreadyLinkedToCaseNumberError);
    }

    /**
     * @Given /^I create a Lay Deputy user account for one of the deputies in the CSV that are not unique$/
     */
    public function iCreateALayDeputyUserAccountForOneOfTheDeputysInTheCSVNotUnique(): void
    {
        $this->iVisitAdminAddUserPage();
        $this->userEmail = 'themightyducks@duck.com';

        $this->fillInField('admin_email', $this->userEmail);
        $this->fillInField('admin_firstname', 'Julie');
        $this->fillInField('admin_lastname', 'Duck');
        $this->fillInField('admin_addressPostcode', 'B1');
        $this->selectOption('admin[roleType]', 'deputy');
        $this->selectOption('admin[roleNameDeputy]', 'ROLE_LAY_DEPUTY');

        $this->pressButton('Save user');

        $this->assertOnAlertMessage('email has been sent to the user');

        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
    }

    /**
     * @Given /^I complete the case manager user registration flow with deputyship details that are not unique$/
     */
    public function iCompleteTheCaseManagerUserRegistrationFlowWithValidDeputyhsipDetailsNotUnique(): void
    {
        $this->setPasswordAndTickTAndCs();

        $this->pressButton('Submit');

        $this->fillField('login_email', $this->interactingWithUserDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillUserDetailsAndSubmit();

        $this->fillInField('client_firstname', $this->faker->firstName());
        $this->fillInField('client_lastname', 'HUEY');
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_address2', 'First Floor');
        $this->fillInField('client_address3', 'Big Building');
        $this->fillInField('client_address4', 'Large Town');
        $this->fillInField('client_address5', 'Notts');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_phone', '01789432876');
        $this->fillInField('client_caseNumber', '31313135');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2016');
        $this->pressButton('client_save');
    }

    /**
     * @Given /^I create a Lay Deputy user account for one of the deputies in the co-deputy CSV$/
     */
    public function iCreateALayDeputyUserAccountForOneOfTheDeputysInTheCSV2()
    {
        $this->iVisitAdminAddUserPage();
        $this->userEmail = 'SOPHIE@FISH.COM';

        $this->fillInField('admin_email', $this->userEmail);
        $this->fillInField('admin_firstname', 'Sophie');
        $this->fillInField('admin_lastname', 'Fish');
        $this->fillInField('admin_addressPostcode', 'B73');
        $this->selectOption('admin[roleType]', 'deputy');
        $this->selectOption('admin[roleNameDeputy]', 'ROLE_LAY_DEPUTY');

        $this->pressButton('Save user');

        $this->assertOnAlertMessage('email has been sent to the user');

        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
    }

    /**
     * @Given /^I complete the case manager user registration flow with other deputy valid deputyship details$/
     */
    public function iCompleteTheCaseManagerUserRegistrationFlowWithOtherDeputyValidDeputyshipDetails()
    {
        $this->deputyUid = '85462400';

        $this->setPasswordAndTickTAndCs();

        $this->pressButton('Submit');

        $this->fillField('login_email', $this->interactingWithUserDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('user_details_firstname', 'Bill');

        $this->fillUserDetailsAndSubmit();

        $this->assertPageContainsText('Add your client\'s details');

        $this->fillInField('client_firstname', $this->faker->firstName());
        $this->fillInField('client_lastname', 'Pilot');
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_address2', 'First Floor');
        $this->fillInField('client_address3', 'Big Building');
        $this->fillInField('client_address4', 'Large Town');
        $this->fillInField('client_address5', 'Notts');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_phone', '01789432876');
        $this->fillInField('client_caseNumber', '1515151P');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2016');
        $this->pressButton('client_save');

        $this->fillInReportDetailsAndSubmit();
    }

    /**
     * @When I invite a Co-Deputy to the service who is already registered
     */
    public function iInviteACoDeputyToTheServiceWhoIsAlreadyRegistered()
    {
        $matches = [];
        preg_match('/[^\/]+$/', $this->getCurrentUrl(), $matches);
        $clientId = $matches[0];

        $this->getCurrentUrl();
        $this->visitPath(sprintf('/codeputy/%s/add', $clientId));

        $coDeputyFirstName = 'Bill';
        $coDeputyLastName = 'Fish';
        $this->coDeputyEmail = 'bill@fish.co.uk';

        $this->fillInField('co_deputy_invite_firstname', $coDeputyFirstName);
        $this->fillInField('co_deputy_invite_lastname', $coDeputyLastName);
        $this->fillInField('co_deputy_invite_email', $this->coDeputyEmail);
        $this->pressButton('co_deputy_invite_submit');
    }

    /**
     * @Then /^they shouldn't be able to register to deputise for a client with already registered details$/
     */
    public function theyShouldNotBeAbleToRegisterToDeputiseForAClientWithAlreadyRegisteredDetails()
    {
        $this->visitPath('/logout');

        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->coDeputyEmail, 'active');
        $this->setPasswordAndTickTAndCs();
        $this->pressButton('set_password_save');

        $this->assertPageContainsText('Sign in to your new account');
        $this->fillInField('login_email', $this->coDeputyEmail);
        $this->fillInField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $this->fillInField('co_deputy_firstname', 'Bill');
        $this->fillInField('co_deputy_lastname', 'Fish');
        $this->fillInField('co_deputy_address1', 'Fieldag');
        $this->fillInField('co_deputy_addressPostcode', 'B73');
        $this->fillInField('co_deputy_addressCountry', 'GB');
        $this->fillInField('co_deputy_phoneMain', '01789432876');
        $this->fillInField('co_deputy_clientLastname', 'Pilot');
        $this->fillInField('co_deputy_clientCaseNumber', '1515151P');

        $this->pressButton('co_deputy_save');
    }

    /**
     * @Then the co-deputy details should be saved to the co-deputy's account
     */
    public function CoDeputyDetailsShouldBeSavedToMyAccount()
    {
        $this->em->flush();
        $this->em->clear();

        /** @var User $coDeputy */
        $coDeputy = $this->em->getRepository(User::class)->findOneBy(
            ['email' => strtolower($this->coDeputyEmail)]
        );

        $this->assertStringEqualsString($this->coDeputyUid, $coDeputy->getDeputyNo(), 'Asserting CoDeputyUid is the same');
        /* Assertion on the new Deputy UID value which is an exact match of the Deputy No value */
        $this->assertIntEqualsInt((int) $this->coDeputyUid, $coDeputy->getDeputyUid(), 'Asserting CoDeputyUid is the same');
        $this->assertStringEqualsString('Fieldag', $coDeputy->getAddress1(), 'Asserting Address Line 1 is the same');
        $this->assertStringEqualsString('Y73', $coDeputy->getAddressPostcode(), 'Asserting Postcode is the same');
        $this->assertStringEqualsString('GB', $coDeputy->getAddressCountry(), 'Asserting Address Country is the same');
        $this->assertStringEqualsString('01789432876', $coDeputy->getPhoneMain(), 'Asserting Main Phone is the same');
    }

    /**
     * @Given /^I search for the co-deputy using their email address$/
     */
    public function iSearchForTheCoDeputyUsingTheirEmailAddress()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->fillField('admin_q', $this->coDeputyEmail);
        $this->pressButton('Search');
    }

    /**
     * @Then /^the co\-deputy should appear in the search results$/
     */
    public function theCoDeputyShouldAppearInTheSearchResults()
    {
        $xpath = '//table[@class="table-govuk-body-s"]/tbody';
        $userResultsTable = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        $this->assertStringContainsString($this->coDeputyEmail, $userResultsTable, 'Results on page');
    }

    /**
     * @Given a Lay Deputy registers to deputise for a client with valid details but invalid reporting period
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithValidDetailsButInvalidReportingPeriod()
    {
        $this->userEmail = 'stuart@cole.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
        $this->deputyUid = '19371940';

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Stuart',
            'Cole',
            $this->userEmail,
            'B73',
            'Billy',
            'Jones',
            '4444444T',
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

        $this->fillInInvalidReportDetailsAndSubmit();
    }

    private function fillInInvalidReportDetailsAndSubmit(): void
    {
        $this->fillInField('report_startDate_day', '01');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2016');
        $this->fillInField('report_endDate_day', '01');
        $this->fillInField('report_endDate_month', '04');
        $this->fillInField('report_endDate_year', '2017');
        $this->pressButton('report_save');
    }

    /**
     * @Then I should see an 'invalid reporting period' error
     */
    public function iShouldSeeReportingPeriodGreaterThanFifteenMonthsError()
    {
        $this->assertOnErrorMessage($this->reportingPeriodGreaterThanFifteenMonths);
    }

    /**
     * @Given /^one of the Lay deputies listed in the lay csv already has an existing account$/
     */
    public function theDeputyListedInTheLayCsvAlreadyHasAnExistingAccount()
    {
        $this->loginToFrontendAs($this->layDeputyCompletedPfaHighAssetsDetails->getUserEmail());

        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaHighAssetsDetails;
        $this->interactingWithUserDetails = $this->layDeputyCompletedPfaHighAssetsDetails->setIsPrimary(true);

        $existingDeputyAccount = $this->em->getRepository(User::class)->findOneBy(['email' => $this->interactingWithUserDetails->getUserEmail()]);
        $existingDeputyAccount->setDeputyUid(35672419);

        $this->em->persist($existingDeputyAccount);
        $this->em->flush();
    }

    /**
     * @Then /^I select the new client from the csv on the Choose a Client page$/
     */
    public function iSelectTheNewClientFromTheCsvOnTheChooseAClientPage()
    {
        $caseNumber = '1717171T';

        $client = $this->em->getRepository(Client::class)->findByCaseNumber($caseNumber);

        $this->visitPath('/client/'.$client->getId());
    }

    /**
     * @Given /^a Lay Deputy registers to deputise for a client with a (\d+) digit case number$/
     */
    public function aLayDeputyRegistersToDeputiseForAClientWithADigitCaseNumber($arg1)
    {
        $this->userEmail = 'maria@vanderquack.co.uk';
        $this->interactingWithUserDetails = new UserDetails(['userEmail' => $this->userEmail]);
        $caseNumber = str_pad('32323232', intval($arg1), '0');

        $this->visitFrontendPath('/register');
        $this->fillInSelfRegistrationFieldsAndSubmit(
            'Maria',
            'Vanderquack',
            $this->userEmail,
            'SW1',
            'Alisha',
            'Dewey',
            $caseNumber,
        );
    }

    /**
     * @Then the report status should be :status
     */
    public function reportStatusShouldBe(string $status)
    {
        $this->iAmOnLayMainPage();
        $this->assertPageContainsText($status);
    }
}
