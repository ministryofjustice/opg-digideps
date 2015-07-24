<?php

namespace DigidepsBehat;

trait EmailTrait
{

    /**
     * @param boolean $throwExceptionIfNotFound
     * @param integer $index 0 = last (default), 1=second last
     * 
     * @return array|null
     */
    private function getEmailMockFromApi($throwExceptionIfNotFound = true, $index = 0)
    {
        $this->visitBehatLink('email-get-last');

        $emailsJson = $this->getSession()->getPage()->getContent();
        $emailsArray = json_decode($emailsJson , true);

        if ($throwExceptionIfNotFound && empty($emailsArray[0]['to'])) {
            throw new \RuntimeException("No email has been sent. Api returned: " . $emailsJson );
        }

        return isset($emailsArray[$index]) ? $emailsArray[$index] : null;
    }

    /**
     * @Given I reset the email log
     */
    public function iResetTheEmailLog()
    {
        $this->visitBehatLink('email-reset');
        $this->assertResponseStatus(200);

        $this->assertNoEmailShouldHaveBeenSent();
    }


    /**
     * @Then no email should have been sent
     */
    public function assertNoEmailShouldHaveBeenSent()
    {
        $content = $this->getEmailMockFromApi(false);
        if ($content) {
            throw new \RuntimeException("Found unexpected email with subject '" . $content['subject'] . "'");
        }
    }

    
    /**
     * @param string $linkPattern
     * @return string link matching the given pattern
     * 
     * @throws \Exception
     */
    private function getFirstLinkInEmailMatching($linkPattern)
    {
        list($links, $mailContent) = $this->getLinksFromEmailHtmlBody();

        $filteredLinks = array_filter($links, function ($element) use ($linkPattern) {
            return preg_match('#'.$linkPattern.'#i', $element);
        });

        if (empty($filteredLinks)) {
            throw new \Exception("no link in the email's body. Filter: $linkPattern . Body:\n $mailContent");
        }
        if (count(array_unique($filteredLinks)) > 1) {
            throw new \Exception("more than one link found in the email's body. Filter: $linkPattern . Links: " . implode("\n", $filteredLinks).". Body:\n $mailContent");
        }
        
        return array_shift($filteredLinks);
    }
    
    
    /**
     * @When I open the :linkPattern link from the email
     */
    public function iOpenTheSpecificLinkOnTheEmail($linkPattern)
    {
        $linkToClick = $this->getFirstLinkInEmailMatching($linkPattern);
        
        // visit the link
        $this->visit($linkToClick);
    }
    
    
    /**
     * @Then the last email containing a link matching :partialLink should have been sent to :to
     */
    public function anEmailContainingALinkMatchingShouldHaveBeenSentTo($partialLink, $to)
    {
        $this->getFirstLinkInEmailMatching($partialLink);
        
        $mail = $this->getEmailMockFromApi();
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }
    
    /**
     * @Then the :which email should have been sent to :to
     */
    public function anEmailShouldHaveBeenSentTo($which, $to)
    {
        if ($which=='last') {
            $index = 0;
        } else if ($which=='second_last') {
            $index = 1;
        } else {
             throw new \RuntimeException("position $which not regognised");
        }
        $mail = $this->getEmailMockFromApi(true, $index);
        $mailTo = key($mail['to']);

        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }


    /**
     * @return array[array links, string mailContent]
     */
    private function getLinksFromEmailHtmlBody()
    {
        $mailContent = $this->getEmailMockFromApi()['parts'][0]['body'];

        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);

        return [$matches[0], $mailContent];
    }
    
    /**
     * @Then the last email should contain :text
     */
    public function mailContainsText($text)
    {
        $mailContent = $this->getEmailMockFromApi()['parts'][0]['body'];

        if (strpos($mailContent, $text) === FALSE) {
            throw new \Exception("Text: $text not found in email. Body: \n $mailContent");
        }
    }

}
