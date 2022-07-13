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
    public function loginToAdminAs(string $email)
    {
        $this->visitAdminPath('/logout');
        $this->visitAdminPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'DigidepsPass1234');

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
     * @When /^I click the (admin |)(activation|password reset) link in the email sent to my address "(.+)"$/
     */
    public function clickActivationOrPasswordResetLinkInEmail($admin, $pageType, $email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

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
        $this->assertRoleIs(USER::ROLE_SUPER_ADMIN, $this->loggedInUserDetails->getUserRole());
    }

    public function assertAdminLoggedIn()
    {
        $this->assertRoleIs(USER::ROLE_ADMIN, $this->loggedInUserDetails->getUserRole());
    }

    public function assertAdminManagerLoggedIn()
    {
        $this->assertRoleIs(USER::ROLE_ADMIN_MANAGER, $this->loggedInUserDetails->getUserRole());
    }

    private function assertRoleIs(string $expectedRole, string $actualRole)
    {
        $isExpectedRole = $actualRole === $expectedRole;

        if (!$isExpectedRole) {
            throw new BehatException(sprintf('Logged in user role is "%s", should be %s', $expectedRole, $actualRole));
        }
    }
}
