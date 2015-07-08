<?php

namespace DigidepsBehat;

trait AuthenticationTrait
{
    
    /**
     * @Given I am logged in as :email with password :password
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email',$email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am logged in to admin as :email with password :password
     */
    public function iAmLoggedInToAdminAsWithPassword($email, $password)
    {
        $this->visitPath('http://digideps-admin.local/logout');
        $this->visitPath('http://digideps-admin.local/login');
        $this->fillField('login_email',$email);
        $this->fillField('login_password', $password);
        $this->pressButton('login_login');
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am not logged into admin
     */
    public function notLoggedInAdmin() {
        $this->visitPath('http://digideps-admin.local/logout');
    }
    
    /**
     * @Given I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        $this->visitPath('/logout');
    }

    /**
     * @Given I am on the login page
     */
    public function iAmAtLogin()
    {
        $this->visitPath('/login');
    }
    
    /**
     * @Then the URL :url should not be accessible
     */
    public function theUrlShouldNotBeAccessible($url)
    {
        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visit($url);
        $this->assertResponseStatus(500);
        $this->visit($previousUrl);
    }
    
    /**
     * @Then I expire the session
     */
    public function iExpireTheSession()
    {
        $this->getSession()->setCookie($this->sessionName, null);
    }   
    
}