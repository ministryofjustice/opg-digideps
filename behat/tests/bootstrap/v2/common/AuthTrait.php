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
        $this->fillField('login_password', 'Abcd1234');
        $this->pressButton('login_login');

        $this->visitPath(sprintf(self::BEHAT_FRONT_USER_DETAILS, $email));

        $activeReportId = json_decode($this->getPageContent(), true)['ActiveReportId'];
        $userId = json_decode($this->getPageContent(), true)['UserId'];

        $this->getSession()->setCookie('ActiveReportId', $activeReportId);
        $this->getSession()->setCookie('UserId', $userId);
    }

    /**
     * @Given :email logs in to admin
     */
    public function loginToAdminAs(string $email)
    {
        $this->visitAdminPath('/logout');
        $this->visitAdminPath('/login');
        $this->fillField('login_email', $email);
        $this->fillField('login_password', 'Abcd1234');
        $this->pressButton('login_login');
    }
}
