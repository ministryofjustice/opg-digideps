<?php

namespace DigidepsBehat\Authentication;

trait AuthenticationTrait
{
    /**
     * @When /^I open the (admin |)(activation|password reset) page for "(.+)"$/
     */
    public function openActivationOrPasswordResetPage($admin, $pageType, $email)
    {
        $url = sprintf('/admin/fixtures/user-registration-token?email=%s', $email);
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'Abcd1234');
        $this->visitAdminPath($url);
        $token = $this->getSession()->getPage()->getContent();
        $this->visitAdminPath('/logout');

        $page = $pageType === 'activation' ? 'activate' : 'password-reset';

        if ($admin === '') {
            $this->visitPath("/user/$page/$token");
        } else {
            $this->visitAdminPath("/user/$page/$token");
        }
    }
}
