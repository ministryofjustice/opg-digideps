<?php

namespace DigidepsBehat;
use Behat\Behat\Tester\Exception\PendingException;

trait CookieTrait
{
    /**
     * @Given I have a cookie policy set
     */
    public function iHaveACookiePolicySet()
    {
        $policy = [];
        $this->getSession()->setCookie('cookie_policy', json_encode($policy));
    }

    /**
     * @Then I should not have a cookie policy
     */
    public function iShouldNotHaveACookiePolicy()
    {
        $policy = json_decode($this->getSession()->getCookie('cookie_policy'));
        if (!is_null($policy)) {
            throw new \Exception('Found unexpected cookie policy');
        }
    }

    /**
     * @Then I should have a cookie policy with usage enabled
     */
    public function iShouldHaveACookiePolicyWithUsageEnabled()
    {
        $policy = json_decode($this->getSession()->getCookie('cookie_policy'));
        if (!$policy->usage) {
            throw new \Exception('Expected cookie usage policy to be enabled, but it is not');
        }
    }

    /**
     * @Then I should have a cookie policy with usage disabled
     */
    public function iShouldHaveACookiePolicyWithUsageDisabled()
    {
        $policy = json_decode($this->getSession()->getCookie('cookie_policy'));
        if ($policy->usage) {
            throw new \Exception('Expected cookie usage policy to be disabled, but it is not');
        }
    }
}
