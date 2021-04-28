<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

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

//        $this->visitPath(sprintf(self::BEHAT_FRONT_USER_DETAILS, $email));
//        $userDetailsArray = json_decode($this->getPageContent(), true);

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

    private function userDetailsExists(string $email)
    {
        foreach ($this->fixtureUsers as $fixtureUser) {
            if ($fixtureUser->getEmail() === $email) {
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
            return strtolower($user->getEmail()) === strtolower($email);
        });

        // Returns the value of the user (so we don't need to know the key) or false if an empty array
        $filteredUser = reset($filteredUsers);

        // We didn't filter the list - the user wasn't found
        if ($filteredUsers === $this->fixtureUsers || !$filteredUser) {
            throw new \Exception(sprintf('User details for email %s not found in $this->fixturesUsers', $email));
        }

        return $filteredUser;
    }
}
