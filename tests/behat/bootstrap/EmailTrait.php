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
    private function getLatestEmailMockFromApi()
    {
        $this->visitBehatLink('email-get-last');
        
        $content =  $this->getSession()->getPage()->getContent();
        $contentJson = json_decode($content, true);
        
        if (empty($contentJson['to'])) {
            throw new \RuntimeException("Email has not been sent. Api returned: " . $content);
        }
        
        return $contentJson;
    }
    
    
    /**
     * @BeforeScenario @cleanMail
     */
    public function beforeScenarioCleanMail(BeforeScenarioScope $scope)
    {
        $this->visitBehatLink('email-reset');
        $this->assertResponseStatus(200);
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
     * @When I open the first link on the email
     */
    public function iOpenTheFirstLinkOnTheEmail()
    {
        $mailContent = $this->getLatestEmailMockFromApi()['parts'][0]['body'];
        
        preg_match_all('#https?://[^\s"<]+#', $mailContent, $matches);
        if (empty($matches[0])) {
            throw new \Exception("no link found in email. Body:\n $mailContent");
        }
        $emails = array_unique($matches[0]);
        if (!count($emails)) {
            throw new \Exception("No links found in the email. Body:\n $mailContent");
        }
        $link = array_shift($emails);

        // visit the link
        $this->visit($link);
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