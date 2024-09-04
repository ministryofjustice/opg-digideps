<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Entity\User;
use App\Tests\Behat\BehatException;

trait AuthTrait
{
    /**
     * @Given :email logs in
     */
    public function loginToFrontendAs(string $email)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');

        $userDetailsArray = $this->fixtureHelper->getLoggedInUserDetails($email);

        if (!$this->userDetailsExists($email)) {
            $this->fixtureUsers[] = new UserDetails($userDetailsArray);
        }

        $this->loggedInUserDetails = $this->getUserDetailsByEmail($email);
    }

    /**
     * @Given :email logs in to admin
     */
    public function loginToAdminAs(string $email, string $password = 'DigidepsPass1234')
    {
        $this->visitAdminPath('/logout');
        $this->visitAdminPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', $password);

        $this->pressButton('login_login');

        $this->loggedInUserDetails = $this->getUserDetailsByEmail($email);
    }

    /**
     * @Given an admin user accesses the admin app
     * @Given a case manager accesses the admin app
     */
    public function adminUsersAccessesAdmin()
    {
        if (empty($this->adminDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $this->adminDetails');
        }

        if ($this->loggedInUserDetails) {
            $this->interactingWithUserDetails = $this->loggedInUserDetails;
        }

        $this->loginToAdminAs($this->adminDetails->getUserEmail());
    }

    /**
     * @Given an admin manager user accesses the admin app
     */
    public function adminManagerUserAccessesAdmin()
    {
        if (empty($this->adminManagerDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $this->adminManagerDetails');
        }

        if ($this->loggedInUserDetails) {
            $this->interactingWithUserDetails = $this->loggedInUserDetails;
        }

        $this->loginToAdminAs($this->adminManagerDetails->getUserEmail());
    }

    /**
     * @Given a super admin user accesses the admin app
     */
    public function superAdminUsersAccessesAdmin()
    {
        if (empty($this->superAdminDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $this->superAdminDetails');
        }

        if ($this->loggedInUserDetails) {
            $this->interactingWithUserDetails = $this->loggedInUserDetails;
        }

        $this->loginToAdminAs($this->superAdminDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy attempts to log into the admin app
     */
    public function aLayDeputyAttemptsToLogIntoTheAdminApp()
    {
        $this->visitAdminPath('/login');
        $this->fillField('login_email', $this->layDeputyNotStartedPfaHighAssetsDetails->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');

        $this->pressButton('login_login');
    }

    /**
     * @Given a super admin user tries to login with an invalid password
     */
    public function superAdminUsersTriesToLoginWithInvalidPassword()
    {
        if (empty($this->superAdminDetails)) {
            throw new BehatException('It looks like fixtures are not loaded - missing $this->superAdminDetails');
        }

        if ($this->loggedInUserDetails) {
            $this->interactingWithUserDetails = $this->loggedInUserDetails;
        }

        $this->loginToAdminAs($this->superAdminDetails->getUserEmail(), 'totallyinvalidpassword');
    }

    private function userDetailsExists(string $email)
    {
        foreach ($this->fixtureUsers as $fixtureUser) {
            if ($fixtureUser->getUserEmail() === $email) {
                return true;
            }
        }

        return false;
    }

    private function getUserDetailsByEmail(string $email)
    {
        // Copy the array so we don't alter it
        $users = array_merge($this->fixtureUsers);

        $filteredUsers = array_filter($users, function ($user) use ($email) {
            return strtolower($user->getUserEmail()) === strtolower($email);
        });

        // Returns the value of the user (so we don't need to know the key) or false if an empty array
        $filteredUser = reset($filteredUsers);

        // We didn't filter the list - the user wasn't found
        if (!$filteredUser) {
            throw new BehatException(sprintf('User details for email %s not found in $this->fixturesUsers', $email));
        }

        return $filteredUser;
    }

    /**
     * @When the user I'm interacting with logs in to the frontend of the app
     */
    public function interactingWithUserLogsInToFrontend()
    {
        $this->assertInteractingWithUserIsSet();

        $this->loginToFrontendAs($this->interactingWithUserDetails->getUserEmail());
    }

    /**
     * @Given /^the user clicks on the registration link sent to their email which has an \'([^\']*)\' token$/
     */
    public function theUserClicksOnTheRegistrationLinkSentToTheirEmailWhichHasAnToken($arg1)
    {
        $this->clickActivationOrPasswordResetLinkInEmail(
            false,
            'password reset',
            $this->interactingWithUserDetails->getUserEmail(),
            $arg1
        );
    }

    /**
     * @When /^I click the (admin |)(activation|password reset) link in the email sent to my address "(.+)"$/
     */
    public function clickActivationOrPasswordResetLinkInEmail($admin, $pageType, $email, $token)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        $this->em->refresh($user);

        if ('expired' === $token) {
            $user->setTokenDate(new \DateTime('-2hours'));
            $this->em->persist($user);
            $this->em->flush($user);
        }

        $token = $user->getRegistrationToken();

        $page = 'activation' === $pageType ? 'activate' : 'password-reset';

        if ('' === $admin || false === $admin) {
            $this->visitPath('/logout');
            $this->visitPath("/user/$page/$token");
        } else {
            $this->visitAdminPath('/logout');
            $this->visitAdminPath("/user/$page/$token");
        }
    }

    public function assertSuperAdminLoggedIn()
    {
        $this->assertRoleIs(User::ROLE_SUPER_ADMIN, $this->loggedInUserDetails->getUserRole());
    }

    public function assertAdminLoggedIn()
    {
        $this->assertRoleIs(User::ROLE_ADMIN, $this->loggedInUserDetails->getUserRole());
    }

    public function assertAdminManagerLoggedIn()
    {
        $this->assertRoleIs(User::ROLE_ADMIN_MANAGER, $this->loggedInUserDetails->getUserRole());
    }

    private function assertRoleIs(string $expectedRole, string $actualRole)
    {
        $isExpectedRole = $actualRole === $expectedRole;

        if (!$isExpectedRole) {
            throw new BehatException(sprintf('Logged in user role is "%s", should be %s', $expectedRole, $actualRole));
        }
    }

    /**
     * @Then their password hash should automatically be upgraded
     */
    public function theirPasswordHashShouldAutomaticallyBeUpgraded()
    {
        $id = $this->interactingWithUserDetails->getUserId();

        $user = $this->em->getRepository(User::class)->find($id);

        $this->em->refresh($user);

        $this->assertStringDoesNotEqualString(
            $this->fixtureHelper->getLegacyPasswordHash(),
            $user->getPassword(),
            'Asserting current password hash does not match legacy password hash'
        );
    }

    /**
     * @Then /^the user sends a request to reset their password$/
     */
    public function theUserSendsARequestToResetTheirPassword()
    {
        $this->fillInField('password_forgotten[email]', $this->interactingWithUserDetails->getUserEmail());
        $this->pressButton('Reset your password');

        $this->assertElementContainsText(
            'body',
            'We have sent a new registration link to your email. Use the link to reset your password.'
        );
    }

    /**
     * @Given /^the user successfully resets their password$/
     */
    public function theUserSuccessfullyResetsTheirPassword()
    {
        $this->fillInField('reset_password_password_first', 'aRandomPassword100');
        $this->fillInField('reset_password_password_second', 'aRandomPassword100');

        $this->pressButton('Save password');
    }

    /**
     * @When /^the user visits an invalid password reset url$/
     */
    public function theUserVisitsAnInvalidPasswordResetUrl()
    {
        $randomToken = 'randomToken00000000000000000000000000000';

        $this->visitPath(sprintf('/user/password-reset/%s', $randomToken));

        $this->assertElementContainsText('body', 'This link is not working or has already been used');
    }

    /**
     * @Then /^a password reset error should be thrown to the user$/
     */
    public function aPasswordResetErrorShouldBeThrownToTheUser()
    {
        $invalidPasswordResetLink = 'This link is not working or has already been used';

        $this->assertElementContainsText('body', $invalidPasswordResetLink);
    }

    /**
     * @Then /^the password reset page should be expired$/
     */
    public function thePasswordResetPageShouldBeExpired()
    {
        $expiredPasswordResetPage = 'This page has expired';

        $this->assertElementContainsText('body', $expiredPasswordResetPage);
    }

    /**
     * @Then /^I should be redirected and denied access to continue$/
     */
    public function IShouldBeRedirectedAndDeniedAccessToContinue()
    {
        $this->assertIntEqualsInt(
            '403',
            $this->getSession()->getStatusCode(),
            'Status code after accessing endpoint'
        );
    }

    /**
     * @Given /^a Lay Deputy tries to login with their non\-primary email address$/
     */
    public function aLayDeputyTriesToLoginWithTheirNonPrimaryEmailAddress()
    {
        $this->visitPath('/login');
        $this->fillField('login_email', $this->layDeputyNotStartedPfaNotPrimaryUser->getUserEmail());
        $this->fillField('login_password', 'DigidepsPass1234');
        $this->pressButton('login_login');
    }

    /**
     * @Then /^they get redirected back to the log in page$/
     */
    public function theyGetRedirectedBackToTheLogInPage()
    {
        $this->iAmOnPage('/login.*$/');
    }

    /**
     * @Given /^a flash message should be displayed to the user$/
     */
    public function aFlashMessageShouldBeDisplayedToTheUser()
    {
        $alertMessage = 'This account has been closed.';

        $xpath = '//div[contains(@class, "govuk-notification-banner__content")]';
        $alertDiv = $this->getSession()->getPage()->find('xpath', $xpath);

        if (is_null($alertDiv)) {
            throw new BehatException('Could not find a div with class "govuk-notification-banner__content"');
        }
        $alertHtml = $alertDiv->getHtml();
        $alertMessageFound = str_contains($alertHtml, $alertMessage);

        if (!$alertMessageFound) {
            throw new BehatException(sprintf('The alert element did not contain the expected message. Expected: "%s", got (full HTML): %s', $alertMessage, $alertHtml));
        }
    }

    /**
     * @Then /^the user tries to access their clients report overview page$/
     */
    public function theUserTriesToAccessTheirClientsReportOverviewPage()
    {
        $activeReportId = $this->layDeputyNotStartedPfaNotPrimaryUser->getCurrentReportId();

        $reportOverviewUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'overview');
        $this->visitPath($reportOverviewUrl);
    }
}
