<?php

namespace DigidepsBehat;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait EmailTrait
{
    private static $postSubmissionFeedbackTemplateID = '862f1ce7-bde5-4397-be68-bd9e4537cff0';
    private static $generalFeedbackTemplateID = '63a25dfa-116f-4991-b7c4-35a79ac5061e';

    private static $mailSentFrom = 'deputy';

    /**
     * @BeforeScenario
     */
    public function resetMailSentFrom(BeforeScenarioScope $scope)
    {
        self::$mailSentFrom = null;
    }

    /**
     * @BeforeScenario @reset-emails
     */
    public function resetMailMock(BeforeScenarioScope $scope)
    {
        $this->visitBehatLink('email-reset');
        $this->visitBehatAdminLink('email-reset');
    }

    /**
     * @Given emails are sent from ":area" area
     */
    public function givenEmailsAreSentFrom($area)
    {
        //$this->visitBehatLink('email-reset');

        self::$mailSentFrom = $area;
    }

    private function getMockedEmails($area = null)
    {
        if ($area === null) {
            $area = self::$mailSentFrom;
        }

        switch ($area) {
            case 'admin':
                $this->visitBehatAdminLink('email-get-last');
                break;
            case 'deputy':
                $this->visitBehatLink('email-get-last');
                break;
            default:
                throw new \Exception('Specify area the email is sent from with [emails are sent from ":area" area]');
        }

        $emailsJson = $this->getSession()->getPage()->getContent();

        if (strpos($emailsJson, '<body>') !== false) {
            $start = strpos($emailsJson, '<body>') + 6;
            $end = strpos($emailsJson, '</body>');
            $emailsJson = substr($emailsJson, $start, ($end - $start));
        }

        return json_decode($emailsJson, true);
    }

    /**
     * @return array|null
     */
    private function getLastEmail($area = null)
    {
        $emailsArray = $this->getMockedEmails($area);

        if (empty($emailsArray[0]['to'])) {
            throw new \RuntimeException('No email has been sent. Api returned: ' . $emailsJson);
        }

        return isset($emailsArray[0]) ? $emailsArray[0] : null;
    }

    /**
     * @param string $regexpr
     *
     * @throws \Exception
     *
     * @return string link matching the given pattern
     *
     */
    private function getFirstLinkInEmailMatching($regexpr)
    {
        list($links, $mailContent) = $this->getLinksFromEmailHtmlBody();

        $filteredLinks = array_filter($links, function ($element) use ($regexpr) {
            return preg_match('#' . $regexpr . '#i', $element);
        });

        if (empty($filteredLinks)) {
            throw new \Exception("no link in the email's body. Filter: $regexpr . Body:\n $mailContent");
        }
        if (count(array_unique($filteredLinks)) > 1) {
            throw new \Exception("more than one link found in the email's body. Filter: $regexpr . Links: " . implode("\n", $filteredLinks) . ". Body:\n $mailContent");
        }

        return array_shift($filteredLinks);
    }

    /**
     * @When I open the :regexpr link from the email
     */
    public function iOpenTheSpecificLinkOnTheEmail($regexpr)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($regexpr);

        $this->visit($linkToClick);
    }

    /**
     * @When I open the :regexpr link from the email on the :area area
     */
    public function iOpenTheLinkFromTheEmailOnTheArea($regexpr, $area)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($regexpr);

        if ($area == 'admin') {
            $linkToClick = str_replace($this->getSiteUrl(), $this->getAdminUrl(), $linkToClick);
        } elseif ($area == 'deputy') {
            $linkToClick = str_replace($this->getAdminUrl(), $this->getSiteUrl(), $linkToClick);
        } else {
            throw new \RuntimeException(__METHOD__ . ": $area not defined");
        }

        $this->visit($linkToClick);
    }

    /**
     * @Then the last email containing a link matching :partialLink should have been sent to :to
     */
    public function anEmailContainingALinkMatchingShouldHaveBeenSentTo($partialLink, $to)
    {
        $this->getFirstLinkInEmailMatching($partialLink);

        $mail = $this->getLastEmail();
        $mailTo = !empty($mail['notifyParams']) ? $mail['to'] : key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }

    /**
     * @Then the last email should have been sent to :to
     */
    public function theLastEmailShouldHaveBeenSentTo($to)
    {
        $mail = $this->getLastEmail();
        $mailTo = !empty($mail['notifyParams']) ? $mail['to'] : key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }

    /**
     * @Then the last :area email should not have been sent to :to
     */
    public function theLastEmailShouldNotHaveBeenSentTo($area, $to)
    {
        $mail = $this->getLastEmail($area);
        $mailTo = !empty($mail['notifyParams']) ? $mail['to'] : key($mail['to']);

        if ($mailTo === $to) {
            throw new \RuntimeException("Last email unexpectedly sent to $to");
        }
    }

    /**
     * @return array[array links, string mailContent]
     */
    private function getLinksFromEmailHtmlBody()
    {
        $mailContent = base64_decode($this->getLastEmail()['parts'][0]['body']);

        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);

        return [$matches[0], $mailContent];
    }

    /**
     * @Then the last email should contain :text
     */
    public function mailContainsText($text)
    {
        $mailContent = base64_decode($this->getLastEmail()['parts'][0]['body']);

        if (strpos($mailContent, $text) === false) {
            throw new \Exception("Text: $text not found in email. Body: \n $mailContent");
        }
    }

    /**
     * @Then no :area email should have been sent to :to
     */
    public function noEmailShouldHaveBeenSentTo($area, $to)
    {
        $mails = $this->getMockedEmails($area);

        if ($mails && count($mails)) {
            foreach ($mails as $mail) {
                $mailTo = !empty($mail['notifyParams']) ? $mail['to'] : key($mail['to']);

                if ($mailTo === $to) {
                    throw new \RuntimeException("Unexpected email sent to $to");
                }
            }
        }
    }

    /**
     * @Then the last email sent should have used the post-submission feedback email template
     */
    public function theLastEmailSentShouldHaveUsedThePostSubmissionFeedbackEmailTemplate()
    {
        $this->assertCorrectTemplateIDUsed(self::$postSubmissionFeedbackTemplateID);
    }

    /**
     * @Then the last email sent should have used the general feedback email template
     */
    public function theLastEmailSentShouldHaveUsedTheGeneralFeedbackEmailTemplate()
    {
        $this->assertCorrectTemplateIDUsed(self::$generalFeedbackTemplateID);
    }

    /**
     * @Then the parameters in the last email sent should include:
     * @param TableNode $expectedParams
     */
    public function theParametersInTheLastEmailSentShouldIncludeParameterWithAValue(TableNode $expectedParams)
    {
        $mail = $this->getLastEmail();
        $notifyParams = $mail['notifyParams'];

        foreach ($expectedParams as $expectedParam) {
            $this->assertNotifyParamExistsWithCorrectValue($notifyParams, $expectedParam['parameter'], $expectedParam['value']);
        }
    }

    private function assertNotifyParamExistsWithCorrectValue($notifyParams, $paramKey, $expectedValue)
    {
        if (!array_key_exists($paramKey, $notifyParams)) {
            throw new \RuntimeException("Last email sent did not contain the expected parameter ${paramKey}");
        }

        $actualValue = $notifyParams[$paramKey];

        if ($actualValue != $expectedValue) {
            throw new \RuntimeException("Last email sent contained the expected parameter '${paramKey}', but the expected value, '${expectedValue}', was '${actualValue}')");
        }
    }

    private function assertCorrectTemplateIDUsed($expectedID)
    {
        $mail = $this->getLastEmail();

        $actualID = $mail['templateID'];

        if ($actualID !== $expectedID) {
            throw new \RuntimeException("Last email sent using template ID '${actualID}' but should have used '${expectedID}''");
        }
    }
}
