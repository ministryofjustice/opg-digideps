<?php

namespace DigidepsBehat\Authentication;

trait AuthenticationTrait
{
    /**
     * @When /^I open the (activation|password reset) page for "(.+)"$/
     */
    public function openActivationOrPasswordResetPage($pageType, $email)
    {
        $url = sprintf('/admin/fixtures/user-registration-token?email=%s', $email);
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'Abcd1234');
        $this->visitAdminPath($url);
        $token = $this->getSession()->getPage()->getContent();

        $page = $pageType === 'activation' ? 'activate' : 'password-reset';

        $this->visitPath("/user/$page/$token");
    }
}
