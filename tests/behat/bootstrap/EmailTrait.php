<?php

namespace DigidepsBehat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait EmailTrait
{
    private static $mailSentFrom = 'deputy';
    
    
    /**
     * @BeforeScenario
     */
    public function resetMailSentFrom(BeforeScenarioScope $scope)
    {
        self::$mailSentFrom = null;
    }
    
    /**
     * @Given I check email sent from ":area" area
     */
    public function ICheckEmailSentFrom($area)
    {
        $this->visitBehatLink('email-reset');
        
        self::$mailSentFrom = $area;
    }
    
    /**
     * @param bool $throwExceptionIfNotFound
     * @param int  $index                    = last (default), 1=second last
     * 
     * @return array|null
     */
    private function getEmailMock($throwExceptionIfNotFound = true, $index = 'last')
    {
        switch (self::$mailSentFrom) {
            case 'admin':
                $this->visitBehatAdminLink('email-get-last');
                break;
            case 'deputy':
                $this->visitBehatLink('email-get-last');
                break;
            default:
                throw new \Exception('Specify area the email is sent from with [I check email sent from ":area" area]');
        }

        $emailsJson = $this->getSession()->getPage()->getContent();

        if (strpos($emailsJson, '<body>') !== false) {
            $start = strpos($emailsJson, '<body>') + 6;
            $end = strpos($emailsJson, '</body>');
            $emailsJson = substr($emailsJson, $start, ($end - $start));
        }

        $emailsArray = json_decode($emailsJson, true);

        if ($throwExceptionIfNotFound && empty($emailsArray[0]['to'])) {
            throw new \RuntimeException('No email has been sent. Api returned: '.$emailsJson);
        }

        // translate index into number
        $map = ['last' => 0, 'second_last' => 1];
        if (!isset($map[$index])) {
            throw new \RuntimeException("position $index not recognised");
        }
        $indexNumber = $map[$index];

        return isset($emailsArray[$indexNumber]) ? $emailsArray[$indexNumber] : null;
    }

    /**
     * @Given I reset the email log
     */
    public function iResetTheEmailLog()
    {
        $this->visitBehatLink('email-reset');
        $this->visitBehatAdminLink('email-reset');
    //    $this->assertResponseStatus(200);

        $this->assertNoEmailShouldHaveBeenSent();
    }

    /**
     * @Then no email should have been sent
     */
    public function assertNoEmailShouldHaveBeenSent()
    {
        $content = $this->getEmailMock(false);
        if ($content) {
            throw new \RuntimeException("Found unexpected email with subject '".$content['subject']."'");
        }
    }

    /**
     * @param string $regexpr
     *
     * @return string link matching the given pattern
     * 
     * @throws \Exception
     */
    private function getFirstLinkInEmailMatching($regexpr)
    {
        list($links, $mailContent) = $this->getLinksFromEmailHtmlBody();

        $filteredLinks = array_filter($links, function ($element) use ($regexpr) {
            return preg_match('#'.$regexpr.'#i', $element);
        });

        if (empty($filteredLinks)) {
            throw new \Exception("no link in the email's body. Filter: $regexpr . Body:\n $mailContent");
        }
        if (count(array_unique($filteredLinks)) > 1) {
            throw new \Exception("more than one link found in the email's body. Filter: $regexpr . Links: ".implode("\n", $filteredLinks).". Body:\n $mailContent");
        }

        return array_shift($filteredLinks);
    }

    /**
     * @When I open the :regexpr link from the email
     */
    public function iOpenTheSpecificLinkOnTheEmail($regexpr)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($regexpr);

        // visit the link
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
            throw new \RuntimeException(__METHOD__.": $area not defined");
        }

        // visit the link
        $this->visit($linkToClick);
    }

    /**
     * @Then the last email containing a link matching :partialLink should have been sent to :to
     */
    public function anEmailContainingALinkMatchingShouldHaveBeenSentTo($partialLink, $to)
    {
        $this->getFirstLinkInEmailMatching($partialLink);

        $mail = $this->getEmailMock();
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '".$mailTo."' does not match the expected '".$to."'");
        }
    }

    /**
     * @Then the :index email should have been sent to :to
     */
    public function theWhichEmailShouldHaveBeenSentTo($index, $to)
    {
        $mail = $this->getEmailMock(true, $index);
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '".$mailTo."' does not match the expected '".$to."'");
        }
    }

    /**
     * @Then the :index email should contain a PDF of at least :minsizekb kb
     */
    public function theEmailAttachmentShouldContain($index, $minsizekb)
    {
        $mail = $this->getEmailMock(true, $index);

        // find body of the part with the given contentType
        $part = array_filter($mail['parts'], function ($part) {
            return $part['contentType'] === 'application/pdf';
        });
        if (empty($part)) {
            throw new \RuntimeException("PDF not found in $index email");
        }
        $pdfBody = base64_decode(array_shift($part)['body']);
        $pdfLen = strlen($pdfBody) / 1024;
        if ($pdfLen < $minsizekb) {
            throw new \RuntimeException("found PDF $pdfLen Kb, must be at least $minsizekb Kb");
        }
    }

    /**
     * @return array[array links, string mailContent]
     */
    private function getLinksFromEmailHtmlBody()
    {
        $mailContent = base64_decode($this->getEmailMock()['parts'][0]['body']);

        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);

        return [$matches[0], $mailContent];
    }

    /**
     * @Then the last email should contain :text
     */
    public function mailContainsText($text)
    {
        $mailContent = base64_decode($this->getEmailMock()['parts'][0]['body']);

        if (strpos($mailContent, $text) === false) {
            throw new \Exception("Text: $text not found in email. Body: \n $mailContent");
        }
    }

    /**
     * @Then the last email should not contain :text
     */
    public function mailNoContainsText($text)
    {
        $mailContent = base64_decode($this->getEmailMock()['parts'][0]['body']);

        if (strpos($mailContent, $text) !== false) {
            throw new \Exception("Text: $text unexpected in email. Body: \n $mailContent");
        }
    }
}
