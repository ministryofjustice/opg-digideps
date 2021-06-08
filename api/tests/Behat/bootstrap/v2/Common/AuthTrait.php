<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

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
     */
    public function adminUsersAccessesAdmin()
    {
        if (empty($this->adminDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $this->adminDetails');
        }

        $this->loginToAdminAs($this->adminDetails->getUserEmail());
    }

    /**
     * @Given an elevated admin user accesses the admin app
     */
    public function elevatedAdminUsersAccessesAdmin()
    {
        if (empty($this->elevatedAdminDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $this->elevatedAdminDetails');
        }

        $this->loginToAdminAs($this->elevatedAdminDetails->getUserEmail());
    }

    /**
     * @Given a super admin user accesses the admin app
     */
    public function superAdminUsersAccessesAdmin()
    {
        if (empty($this->superAdminDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $this->superAdminDetails');
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
            throw new \Exception(sprintf('User details for email %s not found in $this->fixturesUsers', $email));
        }

        return $filteredUser;
    }
}
