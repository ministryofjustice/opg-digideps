<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Registration;

use App\Entity\Organisation;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Tests\Behat\BehatException;

trait ActivateTrait
{
    private ?PreRegistration $existingPreRegistration;
    private array $newUsers = [];
    private string $newUserEmail = '';
    private string $newUserType = '';

    /**
     * @Given /^pre\-registration details exist to allow a lay deputy to register for the service$/
     */
    public function preRegistrationDetailsExistToAllowALayDeputyToRegisterForTheService()
    {
        $this->existingPreRegistration = $this->fixtureHelper->createPreRegistration();
    }

    /**
     * @Given pre-registration details exist with no unicode characters
     */
    public function preRegistrationDetailsExistWithNoUnicodeCharacters()
    {
        $this->existingPreRegistration = $this->fixtureHelper->createPreRegistration('OPG102', 'PFA', 'O\'Shea');
    }

    /**
     * @Given they create a :typeOfUser user with name details that match the pre-registration details
     * @Given a case manager creates an :typeOfUser user
     */
    public function aCaseManagerCreatesALayDeputyUser(string $typeOfUser)
    {
        $this->assertAdminLoggedIn();
        $this->iVisitAdminAddUserPage();

        $this->newUsers += [$this->testRunId => ['type' => $typeOfUser, 'email' => $this->faker->safeEmail()]];

        $lastName = in_array(strtolower($typeOfUser), ['lay', 'ndr']) ? $this->existingPreRegistration->getDeputySurname() : $this->faker->lastName();
        $postCode = in_array(strtolower($typeOfUser), ['lay', 'ndr']) ? $this->existingPreRegistration->getDeputyPostCode() : $this->faker->postcode();
        $roleName = in_array(strtolower($typeOfUser), ['lay', 'ndr']) ? 'ROLE_LAY_DEPUTY' : 'ROLE_PROF_ADMIN';

        $this->fillInField('admin_email', $this->getUserForTestRun()['email']);
        $this->fillInField('admin_firstname', $this->faker->firstName());
        $this->fillInField('admin_lastname', $lastName);
        $this->fillInField('admin_addressPostcode', $postCode);

        $this->selectOption('admin[roleType]', 'deputy');
        $this->selectOption('admin[roleNameDeputy]', $roleName);

        if ('ndr' === $typeOfUser) {
            $this->checkOption('admin_ndrEnabled');
        }

        $this->pressButton('Save user');
        $this->clickLink('Sign out');
    }

    private function getUserForTestRun()
    {
        return $this->newUsers[$this->testRunId];
    }

    /**
     * @When /^the user clicks the activate account link in their email$/
     */
    public function theUserClicksTheActivateAccountLinkInTheirEmail()
    {
        $this->getSession()->reset();
        $this->clickActivationOrPasswordResetLinkInEmail(false, 'activation', $this->getUserForTestRun()['email'], 'active');
    }

    /**
     * @Given /^they complete all except the last step in the registration flow$/
     */
    public function theyCompleteAllExceptTheLastStepInTheRegistrationFlow()
    {
        $this->completeSetPasswordStep();

        if (in_array(strtolower($this->getUserForTestRun()['type']), ['lay', 'ndr'])) {
            $this->loginToFrontendAs($this->getUserForTestRun()['email']);
            $this->completeUserDetailsSection();
        }

        if ('lay' === $this->getUserForTestRun()['type']) {
            $this->completeClientDetailsSection();
        }
    }

    private function completeSetPasswordStep()
    {
        $this->fillInField('set_password_password_first', 'DigidepsPass1234');
        $this->fillInField('set_password_password_second', 'DigidepsPass1234');
        $this->checkOption('set_password_showTermsAndConditions');

        $this->pressButton('set_password_save');
    }

    private function completeUserDetailsSection()
    {
        $this->fillInField('user_details_address1', '102 Petty France');
        $this->fillInField('user_details_addressCountry', 'GB');
        $this->fillInField('user_details_phoneMain', '01789 321234');

        $this->pressButton('user_details_save');
    }

    private function completeClientDetailsSection()
    {
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2020');

        if ($this->getSession()->getPage()->findById('client_caseNumber')) {
            $this->fillInField('client_firstname', $this->faker->firstName());
            $this->fillInField('client_lastname', $this->existingPreRegistration->getClientLastname());
            $this->fillInField('client_caseNumber', $this->existingPreRegistration->getCaseNumber());
        }

        $this->pressButton('client_save');
    }

    private function completeClientDetailsSectionUsingUnicode()
    {
        $this->fillInField('client_address', '1 South Parade');
        $this->fillInField('client_postcode', 'NG1 2HT');
        $this->fillInField('client_country', 'GB');
        $this->fillInField('client_courtDate_day', '01');
        $this->fillInField('client_courtDate_month', '01');
        $this->fillInField('client_courtDate_year', '2020');

        if ($this->getSession()->getPage()->findById('client_caseNumber')) {
            $this->fillInField('client_firstname', $this->faker->firstName());
            $this->fillInField('client_lastname', 'O’Shea');
            $this->fillInField('client_caseNumber', $this->existingPreRegistration->getCaseNumber());
        }

        $this->pressButton('client_save');
    }

    private function completeReportDatesSection()
    {
        $this->fillInField('report_startDate_day', '01');
        $this->fillInField('report_startDate_month', '01');
        $this->fillInField('report_startDate_year', '2020');
        $this->fillInField('report_endDate_day', '31');
        $this->fillInField('report_endDate_month', '12');
        $this->fillInField('report_endDate_year', '2020');

        $this->pressButton('report_save');
    }

    /**
     * @Then /^the partially registered users \'([^\']*)\' should \'([^\']*)\' set$/
     */
    public function thePartiallyRegisteredUsersShouldSet(string $property, string $toBeOrNotToBe)
    {
        $this->fillInField('admin_q', $this->getUserForTestRun()['email']);
        $this->pressButton('Search');

        $headerByProperty = match (strtolower($property)) {
            'active flag' => 'Active',
            'registration date' => 'Registration date'
        };

        $assertionByExpectation = match (strtolower($toBeOrNotToBe)) {
            'not be' => false,
            'be' => true
        };

        switch ($headerByProperty) {
            case 'Active':
                $matchingString = $assertionByExpectation ? 'Yes' : 'No';
                break;
            case 'Registration date':
                $matchingString = $assertionByExpectation ? (new \DateTime())->format('j/m/Y') : 'n.a.';
                break;
            default:
                $supportedProperties = ['Registration date', 'Active flag'];
                throw new BehatException(sprintf('"%s" is not a supported property. Supported properties are %s.', $property, implode(', ', $supportedProperties)));
        }

        $cellValue = $this->getTableCellByUniqueRowValueAndHeader($headerByProperty, $this->getUserForTestRun()['email']);

        if (!str_contains($cellValue, $matchingString)) {
            throw new BehatException(sprintf('Expected "%s" property to be "%s", got "%s"', $headerByProperty, $matchingString, $cellValue));
        }
    }

    private function getColumnPositionByHeader(string $header)
    {
        $tableHeadsXpath = sprintf('//table/thead/tr/th[normalize-space()="%s"]/preceding-sibling::th', $header);

        return count($this->getSession()->getPage()->findAll('xpath', $tableHeadsXpath)) + 1;
    }

    private function getTableCellByUniqueRowValueAndHeader(string $header, string $uniqueValueThatAppearsInRow)
    {
        $positionOfHeader = $this->getColumnPositionByHeader($header);
        $cellByHeaderXpath = sprintf('//td[normalize-space()="%s"]//parent::*/td[%s]', $uniqueValueThatAppearsInRow, $positionOfHeader);

        return $this->getSession()->getPage()->find('xpath', $cellByHeaderXpath)->getHtml();
    }

    /**
     * @When /^the user completes the final registration step$/
     */
    public function theUserCompletesTheFinalRegistrationStep()
    {
        $this->completeFinalRegistrationSection($this->getUserForTestRun()['type']);
    }

    private function completeFinalRegistrationSection($userType)
    {
        $this->loginToFrontendAs($this->getUserForTestRun()['email']);

        sleep(1);

        switch ($userType) {
            case 'lay':
                $this->completeReportDatesSection();
                break;
            case 'org':
                $this->completeOrgUserDetailsSection();
                break;
            case 'ndr':
                $this->completeClientDetailsSection();
                break;
            default:
                throw new BehatException('Only supported userTypes for this step are "lay", "org" or "ndr". Use an available type or add a new one.');
        }
    }

    public function completeOrgUserDetailsSection()
    {
        $this->fillInField('user_details_jobTitle', $this->faker->jobTitle());
        $this->fillInField('user_details_phoneMain', $this->faker->phoneNumber());

        $this->pressButton('user_details_save');
    }

    /**
     * @When /^a lay deputy provides details that match the pre\-registration details$/
     */
    public function aLayDeputyProvidesDetailsThatMatchThePreRegistrationDetails()
    {
        $this->newUsers += [$this->testRunId => ['type' => 'lay', 'email' => $this->faker->safeEmail()]];

        $this->visitFrontendPath('/register');

        $this->fillInField('self_registration_firstname', 'Brian');
        $this->fillInField('self_registration_lastname', $this->existingPreRegistration->getDeputySurname());
        $this->fillInField('self_registration_email_first', $this->getUserForTestRun()['email']);
        $this->fillInField('self_registration_email_second', $this->getUserForTestRun()['email']);
        $this->fillInField('self_registration_postcode', $this->existingPreRegistration->getDeputyPostCode());
        $this->fillInField('self_registration_clientFirstname', 'Billy');
        $this->fillInField('self_registration_clientLastname', $this->existingPreRegistration->getClientLastname());
        $this->fillInField('self_registration_caseNumber', $this->existingPreRegistration->getCaseNumber());

        $this->pressButton('self_registration_save');

        $this->assertPageContainsText('Please check your email');
    }

    /**
     * @When /^the admin user invites a new user to their organisation$/
     */
    public function theAdminUserInvitesANewUserToTheirOrganisation()
    {
        $this->iVisitOrganisationAddUserPageForLoggedInUser();

        $this->newUsers += [$this->testRunId => ['type' => 'org', 'email' => $this->faker->safeEmail()]];

        $this->fillInField('organisation_member_firstname', $this->faker->firstName());
        $this->fillInField('organisation_member_lastname', $this->faker->lastName());
        $this->fillInField('organisation_member_email', $this->getUserForTestRun()['email']);

        $this->selectOption('organisation_member_roleName_0', 'ROLE_PROF_TEAM_MEMBER');

        $this->pressButton('organisation_member_save');
    }

    /**
     * @Given /^they complete the user registration flow using unicode characters$/
     */
    public function theyCompleteTheUserRegistrationFlowUsingUnicodeCharacters()
    {
        $this->completeSetPasswordStep();

        $this->loginToFrontendAs($this->getUserForTestRun()['email']);
        $this->completeUserDetailsSection();

        $this->completeClientDetailsSectionUsingUnicode();

        $this->completeFinalRegistrationSection($this->getUserForTestRun()['type']);
    }
}