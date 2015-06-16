<?php

namespace DigidepsBehat;

trait EmailTrait
{

    /**
     * Array (
      [to] => Array(
      [deputyshipservice@publicguardian.gsi.gov.uk] => test Test
      )

      [from] => Array(
      [admin@digideps.service.dsd.io ] => Digital deputyship service
      )
      [bcc] =>
      [cc] =>
      [replyTo] =>
      [returnPath] =>
      [subject] => Digideps - activation email
      [body] => Hello test Test, click here http://link.com/activate/testtoken to activate your account
      [sender] =>
      [parts] => Array(
      [0] => Array(
      [body] => Hello test Test<br/><br/>click here <a href="http://link.com/activate/testtoken">http://link.com/activate/testtoken</a> to activate your account
      [contentType] => text/html
      )
      )
      )
     * 
     * @retun array
     */
    private function getLatestEmailMockFromApi($throwExceptionIfNotFound = true)
    {
        $this->visitBehatLink('email-get-last');

        $content = $this->getSession()->getPage()->getContent();
        $contentJson = json_decode($content, true);

        if ($throwExceptionIfNotFound && empty($contentJson['to'])) {
            throw new \RuntimeException("Email has not been sent. Api returned: " . $content);
        }

        return $contentJson;
    }

    /**
     * @Given I reset the email log
     */
    public function beforeScenarioCleanMail()
    {
        $this->visitBehatLink('email-reset');
        $this->assertResponseStatus(200);

        $this->assertNoEmailShouldHaveBeenSent();
    }

    /**
     * @Then an email with subject :subject should have been sent to :to
     */
    public function anEmailWithSubjectShouldHaveBeenSentTo($subject, $to)
    {
        $mail = $this->getLatestEmailMockFromApi();
        $mailTo = key($mail['to']);


        if ($mail['subject'] != $subject) {
            throw new \RuntimeException("Subject '" . $mail['subject'] . "' does not match the expected '" . $subject . "'");
        }
        if ($mailTo !== 'the specified email address' && $mailTo != $to) {
            throw new \RuntimeException("Addressee '" . $mailTo . "' does not match the expected '" . $to . "'");
        }
    }

    /**
     * @Then an email with subject :subject should have been sent
     */
    public function anEmailWithSubjectShouldHaveBeenSent($subject)
    {
        $mail = $this->getLatestEmailMockFromApi();

        if ($mail['subject'] != $subject) {
            throw new \RuntimeException("Subject '" . $mail['subject'] . "' does not match the expected '" . $subject . "'");
        }
    }

    /**
     * @Then no email should have been sent
     */
    public function assertNoEmailShouldHaveBeenSent()
    {
        $content = $this->getLatestEmailMockFromApi(false);
        if ($content) {
            throw new \RuntimeException("Found unexpected email with subject '" . $content['subject'] . "'");
        }
    }
    
    /**
     * @When I open the :linkPattern link from the email
     */
    public function iOpenTheSpecificLinkOnTheEmail($linkPattern)
    {
        list($links, $mailContent) = $this->getLinksFromEmailHtmlBody();
        
        if ($linkPattern == 'first') {
            $filteredLinks = $links;
        } else {
            $filteredLinks = array_filter($links, function ($element) use ($linkPattern) {
                return strpos($element, $linkPattern) !== false;
            });
        }
        
        if (empty($filteredLinks)) {
            throw new \Exception("no link in the email's body. Filter: $linkPattern . Body:\n $mailContent");
        }
        if (count(array_unique($filteredLinks)) > 1) {
            throw new \Exception("more than one link found in the email's body. Filter: $linkPattern . Links: " . implode("\n", $filteredLinks).". Body:\n $mailContent");
        }
        $linkToClick = array_shift($filteredLinks);

        // visit the link
        $this->visit($linkToClick);
    }
    
    
    /**
     * @return array[array links, string mailContent]
     */
    private function getLinksFromEmailHtmlBody()
    {
        $mailContent = $this->getLatestEmailMockFromApi()['parts'][0]['body'];

        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);
        
        return [$matches[0], $mailContent];
    }

    
    /**
     * @Then the email should contain :text
     */
    public function mailContainsText($text)
    {
        $mailContent = $this->getLatestEmailMockFromApi()['parts'][0]['body'];

        if (strpos($mailContent, $text) === FALSE) {
            throw new \Exception("Text: $text not found in email. Body: \n $mailContent");
        }
    }

}