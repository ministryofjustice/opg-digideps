<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\UserManagement;

use App\Entity\Organisation;
use App\Entity\User;
use App\Tests\Behat\BehatException;
use App\Tests\Behat\v2\Common\UserDetails;

trait UserManagementTrait
{
    private ?int $userCount = null;
    private array $userEmails = [];
    private array $expectedUsers = [];
    private array $userRoles = [];
    private string $userRole = '';

    // ======= SEARCH FUNCTIONALITY =======

    /**
     * @When I have created the appropriate search test users
     */
    public function iHaveCreatedAppropriateSearchTestUsers()
    {
        $this->createAdditionalDataForUserSearchTests();
    }

    /**
     * @When I search for one of the test users using partial name
     */
    public function iSearchForOneOfTestUsersUsingPartialName()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->fillField('admin_q', 'search-test-');
        $this->pressButton('Search');
        $this->setFixtureUserEmailsAndCount();
    }

    /**
     * @When I search for one of the test users using full name
     */
    public function iSearchForOneOfTestUsersUsingFullName()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->fillField('admin_q', 'search-test-pa-n-'.$this->testRunId.'@t.uk');
        $this->pressButton('Search');
        $this->userCount = 1;
        $this->userEmails = ['search-test-pa-n-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I should see the correct search results
     */
    public function iShouldSeeTheCorrectSearchResult()
    {
        $xpath = '//h2[@id="users-list-title"]/parent::div/p[@class="govuk-body"]';
        $userText = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        $pluralUsers = 1 === $this->userCount ? 'user' : 'users';
        $userMessage = sprintf('found %s %s', strval($this->userCount), $pluralUsers);
        $this->assertStringEqualsString($userMessage, $userText, 'Users Found');
        $xpath = '//table[@class="table-govuk-body-s"]/tbody/tr';
        $userElements = $this->getSession()->getPage()->findAll('xpath', $xpath);
        $userRowCount = count($userElements);
        $this->assertIntEqualsInt($userRowCount, $this->userCount, 'User rows visible');

        foreach ($this->userEmails as $userEmail) {
            $xpath = '//table[@class="table-govuk-body-s"]/tbody';
            $userResultsTable = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
            $this->assertStringContainsString($userEmail, $userResultsTable, 'Results on page');
        }
    }

    /**
     * @When I search for one of the test users with the Lay filter
     */
    public function iSearchForTestUsersWithTheFilter()
    {
        $this->searchUserWithFilter('ROLE_LAY_DEPUTY', 'search-test-');
        $this->userCount = 2;
        $this->userEmails = ['search-test-lay-'.$this->testRunId.'@t.uk', 'search-test-ndr-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Professional filter
     */
    public function iSearchForTestUsersWithTheProfFilter()
    {
        $this->searchUserWithFilter('ROLE_PROF%', 'search-test-');
        $this->userCount = 2;
        $this->userEmails = ['search-test-prof-'.$this->testRunId.'@t.uk', 'search-test-prof-n-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Professional Named filter
     */
    public function iSearchForTestUsersWithTheProfNamedFilter()
    {
        $this->searchUserWithFilter('ROLE_PROF_NAMED', 'search-test-');
        $this->userCount = 1;
        $this->userEmails = ['search-test-prof-n-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Public Authority filter
     */
    public function iSearchForTestUsersWithThePAFilter()
    {
        $this->searchUserWithFilter('ROLE_PA%', 'search-test-');
        $this->userCount = 2;
        $this->userEmails = ['search-test-pa-'.$this->testRunId.'@t.uk', 'search-test-pa-n-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Public Authority Named filter
     */
    public function iSearchForTestUsersWithThePANamedFilter()
    {
        $this->searchUserWithFilter('ROLE_PA_NAMED', 'search-test-');
        $this->userCount = 1;
        $this->userEmails = ['search-test-pa-n-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Admin filter
     */
    public function iSearchForTestUsersWithTheAdminFilter()
    {
        $this->searchUserWithFilter('ROLE_ADMIN', 'search-test-');
        $this->userCount = 1;
        $this->userEmails = ['search-test-admin-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the Super Admin filter
     */
    public function iSearchForTestUsersWithTheSuperFilter()
    {
        $this->searchUserWithFilter('ROLE_SUPER_ADMIN', 'search-test-');
        $this->userCount = 1;
        $this->userEmails = ['search-test-super-'.$this->testRunId.'@t.uk'];
    }

    /**
     * @When I search for one of the test users with the All Roles filter
     */
    public function iSearchForTestUsersWithTheAllFilter()
    {
        $this->searchUserWithFilter('', 'search-test-');
        $this->setFixtureUserEmailsAndCount();
    }

    /**
     * @When I search for one of the NDR test users with the All Roles filter
     */
    public function iSearchForTestUsersWithTheNDRFilter()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->fillField('admin_q', 'search-test-');
        $this->selectOption('admin[role_name]', '');
        $this->checkOption('admin[ndr_enabled]');
        $this->pressButton('Search');
        $this->userCount = 1;
        $this->userEmails = ['search-test-ndr-'.$this->testRunId.'@t.uk'];
    }

    private function setFixtureUserEmailsAndCount()
    {
        $this->userCount = 10;
        $this->userEmails = [
            'search-test-lay-'.$this->testRunId.'@t.uk',
            'search-test-pa-n-'.$this->testRunId.'@t.uk',
            'search-test-pa-'.$this->testRunId.'@t.uk',
            'search-test-prof-n-'.$this->testRunId.'@t.uk',
            'search-test-prof-'.$this->testRunId.'@t.uk',
            'search-test-admin-'.$this->testRunId.'@t.uk',
            'search-test-manager-'.$this->testRunId.'@t.uk',
            'search-test-super-'.$this->testRunId.'@t.uk',
            'search-test-ad-'.$this->testRunId.'@t.uk',
            'search-test-ndr-'.$this->testRunId.'@t.uk',
        ];
    }

    private function searchUserWithFilter(string $filterText, string $searchField)
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->fillField('admin_q', $searchField);
        $this->selectOption('admin[role_name]', $filterText);
        $this->pressButton('Search');
    }

    // ======= CREATE USER FUNCTIONALITY =======

    /**
     * @When I add a new lay deputy user
     */
    public function iAddNewLayDeputyUser()
    {
        $this->pressButton('Add new user');
        $this->expectedUsers = [
            [
                'email' => 'add-user-1-'.$this->testRunId.'@t.com',
                'firstName' => 'added',
                'lastName' => 'user1',
                'postcode' => 'B99 5ZZ',
                'roleType' => 'deputy',
                'role' => 'ROLE_LAY_DEPUTY',
            ],
        ];
        $this->fillInAndSubmitUsers(
            $this->expectedUsers[0]['email'],
            $this->expectedUsers[0]['firstName'],
            $this->expectedUsers[0]['lastName'],
            $this->expectedUsers[0]['postcode'],
            $this->expectedUsers[0]['roleType'],
            $this->expectedUsers[0]['role']
        );
    }

    /**
     * @When I search for the newly created user
     */
    public function iSearchForNewlyCreatedUser()
    {
        $this->searchUserWithFilter('', $this->expectedUsers[0]['email']);
    }

    /**
     * @Then I can see the user as non active in search results
     */
    public function iCanSeeTheUserAsNonActiveInSearchResults()
    {
        $this->iAmOnAdminUsersSearchPage();
        $xpath = '//tbody/tr';
        $firstRow = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        $this->assertStringContainsString($this->expectedUsers[0]['firstName'], $firstRow, 'Add user check - first name');
        $this->assertStringContainsString($this->expectedUsers[0]['lastName'], $firstRow, 'Add user check - last name');
        $this->assertStringContainsString($this->expectedUsers[0]['email'], $firstRow, 'Add user check - email');
        $this->assertStringContainsString('(Re)send activation email', $firstRow, 'Add user check - activation text');
    }

    private function createArrayOfAddUsersFromArray($rolesArray)
    {
        foreach ($rolesArray as $key => $role) {
            $email = 'add-user-'.$this->userRole.'-'.$role['roleName'].'-'.$this->testRunId.'@t.uk';
            $this->expectedUsers[] = [
                'email' => $email,
                'firstName' => 'added',
                'lastName' => 'user'.$role['roleName'].$this->userRole,
                'postcode' => 'B'.strval($key).' 1ZZ',
                'roleType' => $role['roleType'],
                'role' => $role['role'],
            ];
            $this->userEmails[] = $email;
        }
    }

    private function createArrayOfEditUsersFromArray($rolesArray)
    {
        foreach ($rolesArray as $key => $role) {
            $email = 'edit-test-'.$role['roleName'].'-'.$this->testRunId.'@t.uk';
            $this->expectedUsers[] = [
                'email' => $email,
                'firstName' => 'first'.str_replace('_', '', strtolower($role['role'])),
                'lastName' => 'last'.str_replace('_', '', strtolower($role['role'])),
                'postcode' => 'B'.strval($key).' 1YY',
                'roleType' => $role['roleType'],
                'role' => $role['role'],
            ];
            $this->userEmails[] = $email;
        }
    }

    private function addUserInApplication()
    {
        $this->userCount = 0;
        foreach ($this->expectedUsers as $addedUser) {
            $this->iNavigateToAddNewUser();
            $this->iAmOnAdminAddUserPage();
            $this->checkRolesExistToAdd($this->userRoles);
            $this->fillInAndSubmitUsers(
                $addedUser['email'],
                $addedUser['firstName'],
                $addedUser['lastName'],
                $addedUser['postcode'],
                $addedUser['roleType'],
                $addedUser['role']
            );
            ++$this->userCount;
        }
    }

    /**
     * @When I add invalid details in each of the fields
     */
    public function iAddInvalidDetailsInEachField()
    {
        $this->fillInAndSubmitUsers(
            'invalidemailwithoutatsymbol.com',
            'This is a really long string that makes this invalid',
            'This is a really long string that makes this invalid',
            'POSTCODE TOO LONG',
            'deputy',
            'ROLE_LAY_DEPUTY'
        );
    }

    /**
     * @When I get the correct validation messages for invalid user
     */
    public function iGetCorrectValidationMessagesForInvalidUser()
    {
        $this->assertOnAlertMessage('This email is not valid');
        $this->assertOnAlertMessage('The first name cannot be longer than 50 letters');
        $this->assertOnAlertMessage('The last name cannot be longer than 50 letters');
        $this->assertOnAlertMessage('The postcode cannot be longer than 10 characters');
    }

    private function checkRolesExistToAdd($roles)
    {
        $options = $this->getSession()->getPage()->findAll('xpath', '//option');
        $expectedOptions = [];
        foreach ($options as $option) {
            $expectedOptions[] = $option->getValue();
        }
        foreach ($roles as $role) {
            $exists = in_array($role['role'], $expectedOptions);
            $this->assertBoolIsTrue($exists, 'Role: '.$role['role'].' in list');
        }
    }

    private function setUniversalUserRoles()
    {
        $this->userRoles = [
            ['role' => 'ROLE_LAY_DEPUTY', 'roleName' => 'lay', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PA_NAMED', 'roleName' => 'pa', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PA_ADMIN', 'roleName' => 'pa-admin', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PA_TEAM_MEMBER', 'roleName' => 'pa-team', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PROF_NAMED', 'roleName' => 'prof-named', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PROF_ADMIN', 'roleName' => 'prof-admin', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PROF_TEAM_MEMBER', 'roleName' => 'prof-team', 'roleType' => 'deputy'],
            ['role' => 'ROLE_ADMIN', 'roleName' => 'admin', 'roleType' => 'staff'],
        ];
    }

    /**
     * @When I add each of the available user types for a super admin
     */
    public function iAddEachUserTypeForSuperAdmin()
    {
        $this->userRole = 'super';
        $this->setUniversalUserRoles();
        $this->userRoles[] = ['role' => 'ROLE_ADMIN_MANAGER', 'roleName' => 'manager', 'roleType' => 'staff'];
        $this->userRoles[] = ['role' => 'ROLE_SUPER_ADMIN', 'roleName' => 'super', 'roleType' => 'staff'];
        $this->createArrayOfAddUsersFromArray($this->userRoles);
        $this->addUserInApplication();
    }

    /**
     * @When I check we can add the appropriate user types for an admin manager
     */
    public function iCheckCanAddEachUserTypeForAdminManager()
    {
        $this->userRole = 'manager';
        $this->setUniversalUserRoles();
        $this->iNavigateToAddNewUser();
    }

    /**
     * @When I check we can add the appropriate user types for an admin
     */
    public function iCheckCanAddEachUserTypeForAdmin()
    {
        $this->userRole = 'admin';
        $this->setUniversalUserRoles();
        $this->iNavigateToAddNewUser();
    }

    /**
     * @Then I see the appropriate user types available to add
     */
    public function iSeeUserTypesAvailableToAddAsNewUser()
    {
        $this->checkRolesExistToAdd($this->userRoles);
        $this->clickLink('Cancel');
        $this->iAmOnAdminUsersSearchPage();
    }

    /**
     * @Then I should see each created users in the search window
     */
    public function iSeeEachCreatedUsersInSearchWindow()
    {
        $this->searchUserWithFilter('', 'add-user-'.$this->userRole.'-');
        $this->iShouldSeeTheCorrectSearchResult();
    }

    /**
     * @When I resend activation email
     */
    public function iResendActivationEmail()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->clickLink('(Re)send activation email');
    }

    /**
     * @Then I see that activation link has been sent
     */
    public function iSeeActivationLinkHasBeenSent()
    {
        $xpath = '//body/p';
        $response = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        $this->assertStringContainsString('[Link sent]', $response, 'Add user check - link sent');
    }

    /**
     * @Then I see that an activation email has been sent to the user
     */
    public function iSeeActivationEmailHasBeenSent()
    {
        $this->assertOnAlertMessage('An activation email has been sent to the user');
    }

    // ======= EDIT USER FUNCTIONALITY =======

    /**
     * @When I have created the appropriate test users to edit
     */
    public function iHaveCreatedTheAppropriateTestUsersToEdit()
    {
        $this->createAdditionalDataForUserEditTests();
    }

    private function fillInAndSubmitUsers($email, $firstName, $lastName, $postcode, $roleType, $role)
    {
        $this->iAmOnAdminAddUserPage();
        $selectType = 'deputy' == $roleType ? 'admin[roleNameDeputy]' : 'admin[roleNameStaff]';
        $this->fillField('admin[email]', $email);
        $this->fillField('admin[firstname]', $firstName);
        $this->fillField('admin[lastname]', $lastName);
        $this->fillField('admin[addressPostcode]', $postcode);
        $this->selectOption('admin[roleType]', $roleType);
        $this->selectOption($selectType, $role);
        $this->pressButton('Save user');
    }

    /**
     * @When I edit each of the test users
     */
    public function iEditEachOfTheTestUsers()
    {
        $this->generateEditExistingUserArray();
        foreach ($this->expectedUsers as $key => $addedUser) {
            $this->iAmOnAdminUsersSearchPage();
            $this->searchUserWithFilter('', $addedUser['email']);
            // update the email now we have searched for it...
            $this->expectedUsers[$key]['email'] = $addedUser['email'].'.edit';
            // click on the first result (should only be one)
            $this->iClickOnFirstUserReturnedBySearch();
            $this->iNavigateToEditUser();
            $this->fillFieldsAndSubmitOnEditExistingUser($this->expectedUsers[$key]);
            $this->clickLink('Cancel');
        }
    }

    /**
     * @Then I should see the users have been correctly updated
     */
    public function iShouldSeeUsersCorrectlyUpdated()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->searchUserWithFilter('', 'edit-test-');
        $xpath = '//table[@class="table-govuk-body-s"]/tbody';
        $resultsTable = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        foreach ($this->expectedUsers as $addedUser) {
            $this->assertStringContainsString($addedUser['firstName'], $resultsTable, 'Edit user check - first name');
            $this->assertStringContainsString($addedUser['lastName'], $resultsTable, 'Edit user check - last name');
            $this->assertStringContainsString($addedUser['email'], $resultsTable, 'Edit user check - email');
        }
    }

    /**
     * @When I navigate to the lay user for edit tests
     */
    public function iNavigateToLayUserForEditTests()
    {
        $this->iAmOnAdminUsersSearchPage();
        $layDeputy = $this->expectedUsers[0];
        $this->searchUserWithFilter('', $layDeputy['email']);
        $this->iClickOnFirstUserReturnedBySearch();
        $this->iNavigateToEditUser();
    }

    /**
     * @Then I see user details are displayed correctly
     */
    public function iSeeUserDetailsAreDisplayedCorrectly()
    {
        $layDeputy = $this->expectedUsers[0];
        $firstName = $this->getSession()->getPage()->find('xpath', '//input[@id="admin_firstname"]')->getValue();
        $lastName = $this->getSession()->getPage()->find('xpath', '//input[@id="admin_lastname"]')->getValue();
        $email = $this->getSession()->getPage()->find('xpath', '//input[@id="admin_email"]')->getValue();
        $postcode = $this->getSession()->getPage()->find('xpath', '//input[@id="admin_addressPostcode"]')->getValue();
        $this->assertStringEqualsString($layDeputy['firstName'], $firstName, 'Edit user - first name check');
        $this->assertStringEqualsString($layDeputy['lastName'], $lastName, 'Edit user - last name check');
        $this->assertStringEqualsString($layDeputy['email'], $email, 'Edit user - email check');
        $this->assertStringEqualsString($layDeputy['postcode'], $postcode, 'Edit user - postcode check');
    }

    /**
     * @When I view the super admin user
     */
    public function iViewSuperAdminUser()
    {
        $this->iAmOnAdminUsersSearchPage();
        $this->searchUserWithFilter('', 'edit-test-super-'.$this->testRunId.'@t.uk');
        $this->iClickOnFirstUserReturnedBySearch();
    }

    /**
     * @When I view the admin manager user
     */
    public function iViewAdminManagerUser()
    {
        $this->iVisitAdminSearchUserPage();
        $this->iAmOnAdminUsersSearchPage();
        $this->searchUserWithFilter('', 'edit-test-manager-'.$this->testRunId.'@t.uk');
        $this->iClickOnFirstUserReturnedBySearch();
        $this->iAmOnAdminViewUserPage();
    }

    /**
     * @When I view the admin user
     */
    public function iViewAdminUser()
    {
        $this->iVisitAdminSearchUserPage();
        $this->iAmOnAdminUsersSearchPage();
        $this->searchUserWithFilter('', 'edit-test-admin-'.$this->testRunId.'@t.uk');
        $this->iClickOnFirstUserReturnedBySearch();
    }

    /**
     * @Then I should not be able to edit that user
     */
    public function iShouldNotBeAbleToEditThatUser()
    {
        $this->iAmOnAdminViewUserPage();
        $xpath = "//a[text()[contains(., 'Edit user')]]";
        $link = $this->getSession()->getPage()->find('xpath', $xpath);
        $this->assertIsNull($link, 'Edit button present');
    }

    /**
     * @Then I should be able to delete that user
     */
    public function iShouldBeAbleToDeleteThatUser()
    {
        $this->iAmOnAdminViewUserPage();
        $this->clickBasedOnText('Delete user');
        $this->iAmOnAdminDeleteConfirmUserPage();
    }

    /**
     * @Then I should be able to edit that user
     */
    public function iShouldBeAbleToEditThatUser()
    {
        $this->iAmOnAdminViewUserPage();
        $this->clickBasedOnText('Edit user');
        $this->iAmOnAdminEditUserPage();
        $this->iClickOnNthElementBasedOnRegex('/admin\/$/', 0);
    }

    /**
     * @When I delete the admin manager
     */
    public function iDeleteTheAdminManager()
    {
        $this->deleteAdmin('manager');
    }

    /**
     * @When I delete the admin
     */
    public function iDeleteTheAdmin()
    {
        $this->deleteAdmin('admin');
    }

    /**
     * @Then I no longer see the admin manager in search results
     */
    public function iNoLongerSeeAdminManagerInSearchResults()
    {
        $this->noResultsReturnedForUserType('manager');
    }

    /**
     * @Then I no longer see the admin in search results
     */
    public function iNoLongerSeeAdminInSearchResults()
    {
        $this->noResultsReturnedForUserType('admin');
    }

    private function noResultsReturnedForUserType($userType)
    {
        $this->iVisitAdminSearchUserPage();
        $this->iAmOnAdminUsersSearchPage();
        $email = 'edit-test-'.$userType.'-'.$this->testRunId.'@t.uk';
        $this->searchUserWithFilter('', $email);
        $xpath = '//tbody';
        $tbody = $this->getSession()->getPage()->find('xpath', $xpath)->getHtml();
        $this->assertStringDoesNotContainString($email, $tbody, 'Delete test - '.$userType);
    }

    private function deleteAdmin($userType)
    {
        $this->iVisitAdminSearchUserPage();
        $this->iAmOnAdminUsersSearchPage();
        $this->searchUserWithFilter('', 'edit-test-'.$userType.'-'.$this->testRunId.'@t.uk');
        $this->iClickOnFirstUserReturnedBySearch();
        $this->clickBasedOnText('Edit user');
        $this->iAmOnAdminEditUserPage();
        $this->clickBasedOnText('Delete user');
        $this->iAmOnAdminDeleteConfirmUserPage();
        $this->clickLink('Yes, I\'m sure');
    }

    private function generateEditExistingUserArray()
    {
        $this->userRoles = [
            ['role' => 'ROLE_LAY_DEPUTY', 'roleName' => 'lay', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PA_NAMED', 'roleName' => 'pa', 'roleType' => 'deputy'],
            ['role' => 'ROLE_PROF_ADMIN', 'roleName' => 'prof', 'roleType' => 'deputy'],
            ['role' => 'ROLE_ADMIN', 'roleName' => 'admin', 'roleType' => 'staff'],
            ['role' => 'ROLE_SUPER_ADMIN', 'roleName' => 'super', 'roleType' => 'staff'],
            ['role' => 'ROLE_ADMIN_MANAGER', 'roleName' => 'manager', 'roleType' => 'staff'],
        ];
        $this->createArrayOfEditUsersFromArray($this->userRoles);
    }

    private function iNavigateToEditUser()
    {
        $this->iAmOnAdminViewUserPage();
        $this->clickBasedOnText('Edit user');
    }

    private function fillFieldsAndSubmitOnEditExistingUser($addedUser)
    {
        $this->iAmOnAdminEditUserPage();
        $this->fillField('admin[email]', $addedUser['email']);
        $this->fillField('admin[firstname]', $addedUser['firstName']);
        $this->fillField('admin[lastname]', $addedUser['lastName']);
        $this->fillField('admin[addressPostcode]', $addedUser['postcode']);
        $this->pressButton('Update user');
    }

    private function iClickOnFirstUserReturnedBySearch()
    {
        $this->iClickOnNthElementBasedOnRegex('/admin\/user\/[0-9].*$/', 0);
    }

    /**
     * @When /^the user visits the forgotten your password page$/
     */
    public function theUserVisitsTheForgottenYourPasswordPage()
    {
        $this->visitsTheForgottenYourPasswordPage();
    }

    /**
     * @Then /^I can only view my user details$/
     */
    public function iCanOnlyViewMyUserDetails()
    {
        $orgUserEmail = $this->interactingWithUserDetails->getUserEmail();

        $xpath = '//div[contains(@class, "govuk-summary-list__row")]';

        $listSummaryRows = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $formattedDataElements = [];

        foreach ($listSummaryRows as $row) {
            $formattedDataElements[] = strtolower($row->getText());
        }

        $expectedRowCount = 2;
        $this->assertIntEqualsInt($expectedRowCount, count($formattedDataElements), 'Row count for users email and password data only');

        $this->assertStringContainsString($orgUserEmail, $formattedDataElements[0], 'Asserting users email address found on page');
    }

    /**
     * @Then /^I should be able to add a new user to the organisation$/
     */
    public function iShouldBeAbleToAddANewUserToTheOrganisation()
    {
        $newUser = [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->email(),
        ];

        $this->fillField('organisation_member_firstname', $newUser['firstName']);
        $this->fillField('organisation_member_lastname', $newUser['lastName']);
        $this->fillField('organisation_member_email', $newUser['email']);

        $this->selectOption('organisation_member[roleName]', 'ROLE_PROF_ADMIN');
        $this->pressButton('Save');

        $this->iAmOnOrgSettingsPage();

        $this->assertElementContainsText('table', $newUser['firstName'].' '.$newUser['lastName']);
        $this->assertElementContainsText('table', $newUser['email']);
    }

    /**
     * @Then /^I attempt to remove an org user$/
     */
    public function iAttemptToRemoveAnOrgUser()
    {
        $orgUsersArray = $this->getAllOrgUsers()['users'];

        $orgUserToBeDeletedEmail = '';

        foreach ($orgUsersArray as $orgUser) {
            if ($orgUser !== $this->loggedInUserDetails->getUserEmail()) {
                $orgUserToBeDeletedEmail = $this->em->getRepository(User::class)->find($orgUser['id'])->getEmail();
            }
        }

        $this->assertElementContainsText('table', $orgUserToBeDeletedEmail);

        // user with admin permissions can only remove other users, they can't remove themselves on this page
        $this->clickLink('Remove');
        $this->pressButton('Yes, remove user from this organisation');
    }

    /**
     * @Then /^the user should be deleted from the organisation$/
     */
    public function theUserShouldBeDeletedFromTheOrganisation()
    {
        $orgUsersArray = $this->getAllOrgUsers()['users'];

        $xpath = '//tr[contains(@class, "govuk-table__row behat")]';

        $listSummaryRows = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $formattedDataElements = [];

        foreach ($listSummaryRows as $row) {
            $formattedDataElements[] = strtolower($row->getText());
        }

        $expectedRowCount = 1;
        $this->assertIntEqualsInt($expectedRowCount, count($formattedDataElements), 'Only one row now exists for the logged in user');

        $expectedOrgUsers = 1;
        $this->assertIntEqualsInt($expectedOrgUsers, count($orgUsersArray), 'Only one user now exists in the organisation');
    }

    /**
     * @Then /^I can view the other org user but I cannot \'([^\']*)\' them$/
     */
    public function iCanViewTheOtherOrgUserButICannotThem($arg1)
    {
        $orgIdAndUsersArray = $this->getAllOrgUsers();

        $otherOrgUserDetails = [];

        foreach ($orgIdAndUsersArray['users'] as $orgUser) {
            if ($orgUser !== $this->loggedInUserDetails->getUserEmail()) {
                $otherOrgUserDetails[] = $orgUser['id'];
                $otherOrgUserDetails[] = $orgUser['email'];
            }
        }

        $this->assertElementContainsText('table', $otherOrgUserDetails[1]);

        $xpathLocator = sprintf(
            "//a[contains(@href,'/org/settings/organisation/%s/delete-user/%s')]",
            $orgIdAndUsersArray['id'],
            $otherOrgUserDetails[0]
        );

        !$this->getSession()->getPage()->find('xpath', $xpathLocator);
        $this->assertElementNotContainsText('table', '$arg1');
    }

    /**
     * @Given /^I click to edit the other org user$/
     */
    public function iClickToEditTheOtherOrgUser()
    {
        // identify the id of the user to be edited
        $orgIdAndUsersArray = $this->getAllOrgUsers();

        $orgUserIdToBeEdited = '';

        foreach ($orgIdAndUsersArray['users'] as $orgUser) {
            if ($orgUser !== $this->loggedInUserDetails->getUserEmail()) {
                $orgUserIdToBeEdited = $orgUser['id'];
            }
        }

        $xpathLocator = sprintf(
            "//a[contains(@href,'/org/settings/organisation/%s/edit/%s')]",
            $orgIdAndUsersArray['id'],
            $orgUserIdToBeEdited
        );

        $this->getSession()->getPage()->find('xpath', $xpathLocator)->click();
    }

    private function getAllOrgUsers()
    {
        $orgEmailIdentifier = $this->loggedInUserDetails->getOrganisationEmailIdentifier();

        $orgUsersArray = [];

        $orgId = $this->em->getRepository(Organisation::class)->findByEmailIdentifier($orgEmailIdentifier)->getId();

        $orgUsersArray['id'] = $orgId;

        $orgUsersArray['users'] = $this->em->getRepository(Organisation::class)->findArrayById($orgId)['users'];

        return $orgUsersArray;
    }

    /**
     * @When /^I edit the users account details$/
     */
    public function iEditTheUsersAccountDetails()
    {
        $this->iAmonOrgSettingsEditAnotherUserPage();
        $this->fillInField('organisation_member[firstname]', $this->faker->lastName(), 'firstname');
        $this->pressButton('Save');
    }

    /**
     * @Then /^the user should be updated$/
     */
    public function theUserShouldBeUpdated()
    {
        $this->iAmOnOrgUserAccountsPage();

        $this->assertOnAlertMessage('The user has been edited');
    }

    /**
     * @When I search for one of the org users using :whichName name
     */
    public function iSearchForOneOfOrgUsersUsingName($whichName)
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminCombinedHighNotStartedDetails : $this->interactingWithUserDetails;

        $searchName = $this->getSearchTerm($whichName);

        $this->searchForUserBy($searchName, $user);
    }

    private function searchForUserBy(string $searchTerm, UserDetails $userDetailsInteractingWith)
    {
        $this->fillField('search_users_q', $searchTerm);
        $this->pressButton('Search');

        $this->interactingWithUserDetails = $userDetailsInteractingWith;
    }

    /**
     * @Then I should see :occurances user details in the user list results with the same :whichName name
     */
    public function iShouldSeeBothUserDetailsInResults(int $occurances, string $whichName)
    {
        $this->userCount = $occurances;
        $this->iShouldSeeNUserWithSameName($whichName);
    }

    private function iShouldSeeNUserWithSameName(string $whichName)
    {
        $this->assertUserCountSet();

        $searchResults = $this->getSearchResults();

        $searchName = $this->getSearchTerm($whichName);
        $searchResultsFound = implode(',', $searchResults);
        $userNamesFoundCount = substr_count($searchResultsFound, strtolower($searchName));

        if ($userNamesFoundCount < $this->userCount) {
            throw new BehatException(sprintf('The user search results list did not contain the required occurrences of the users full name. Expected: "%s" (at least %s times), got (full HTML): %s', $searchName, $this->userCount, $userNamesFoundCount));
        }

        $this->assertIntEqualsInt($userNamesFoundCount, $this->userCount, 'User rows visible');
    }

    private function assertUserCountSet()
    {
        if (is_null($this->userCount)) {
            throw new BehatException(sprintf("You're attempting to run a step definition that requires this->userCount to be set but its null. Set it and try again."));
        }
    }

    private function getSearchResults()
    {
        $xpath = '//td';
        $tableDataElements = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $formattedDataElements = [];

        foreach ($tableDataElements as $td) {
            $formattedDataElements[] = strtolower($td->getText());
        }

        return $formattedDataElements;
    }

    private function getSearchTerm(string $whichName)
    {
        switch (strtolower($whichName)) {
            case 'first':
                $searchName = $this->interactingWithUserDetails->getUserFirstName();
                break;
            case 'last':
                $searchName = $this->interactingWithUserDetails->getUserLastName();
                break;
            case 'full':
                $searchName = sprintf(
                    '%s %s',
                    $this->interactingWithUserDetails->getUserFirstName(),
                    $this->interactingWithUserDetails->getUserLastName()
                );
                break;
            default:
                throw new BehatException('This step only supports "first|last|full" as a search term. Either update step argument or add a case statement.');
        }

        return $searchName;
    }
}
