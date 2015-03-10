<?php

namespace DigidepsBehat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Behat context class.
 * 
 * when the alpha models are refactored and simplified, this class can be refactored and splitter around.
 * until then, better to keep things in the sample place for simplicity
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use RegionTrait;

    use DebugTrait;

    use PdfTrait;

    use LogTrait;

    use StatusSnapshotTrait;
    
    use KernelDictionary;
    
    public function __construct(array $options)
    {
        //$options['session']; // not used
        ini_set('xdebug.max_nesting_level', $options['maxNestingLevel'] ?: 200);
        ini_set('max_nesting_level', $options['maxNestingLevel'] ?: 200);
//        if (!empty($options['set_time_limit'])) {
//            set_time_limit($options['set_time_limit']);
//        }
    }
    
    
    public function setKernel(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    protected function getSymfonyParam($name)
    {
        $this->getContainer()->getParameter($name);
    }

    
    /**
     * @BeforeSuite
     */
     public static function prepare(\Behat\Testwork\Hook\Scope\BeforeSuiteScope $scope)
     {
         $suiteName = $scope->getSuite()->getName();
         echo "\n\n"
              . strtoupper($suiteName) . "\n"
              . str_repeat('=', strlen($suiteName)) . "\n"
              . $scope->getSuite()->getSetting('description') . "\n"
              . "\n";
     }
    
    /**
     * @Given I am logged in as :email with password :password
     */
    public function iAmLoggedInAsWithPassword($email, $password)
    {
        $this->visitPath('/logout');
        $this->visitPath('/login');
        $this->fillField('login_email',$email);
        $this->fillField('login_password', $password);
        $this->iSubmitTheForm();
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        $this->visitPath('/logout');
    }

    /**
     * @When I go to the report page
     */
    public function iGoToTheReportPage()
    {
        $this->visit('/');
        $this->clickLinkInsideElement('open', 'reports');
    }

    /**
     * @When I go to the account page
     */
    public function iGoToTheAccountPage()
    {
        $this->iGoToTheReportPage();
        $this->clickLinkInsideElement('account-open', 'report-dashboard');
    }
    
    /**
     * @When I go to the account balance page
     */
    public function iGoToTheAccountBalancePage()
    {
        $this->iGoToTheAccountPage();
        $this->clickLinkInsideElement("income-balance", "account-tabs");
    }
    
     /**
     * @When I go to the manage users page
     */
    public function iGoToTheManageUsersPage()
    {
        $this->visit('/admin/users');
    }
    
    /**
     * @Given I submit the form
     */
    public function iSubmitTheForm()
    {
        $linkSelector = 'button[type=submit]';
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $linkSelector);
        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than a $linkSelector element in the page. Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("Element $linkSelector not found. Interrupted");
        }
        
        // click on the found link
        $linksElementsFound[0]->click();
    }
    
    
    /**
     * @Then the page title should be :text
     */
    public function thePageTitleShouldBe($text)
    {
        $this->iShouldSeeInTheRegion($text, 'page-title');
    }
    
    
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
        $this->visit('behat/email-get-last');
        
        $content =  $this->getSession()->getPage()->getContent();
        $contentJson = json_decode($content, true);
        
        if (empty($contentJson['to'])) {
            throw new \RuntimeException("Email has not been sent");
        }
        
        return $contentJson;
    }
    
    
    /**
     * @BeforeScenario @cleanMail
     */
    public function beforeScenarioCleanMail(BeforeScenarioScope $scope)
    {
        $this->visit('behat/reset');
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
     * @Then the form should contain an error
     */
    public function theFormShouldContainAnError()
    {
        $this->iShouldSeeTheRegion('form-errors');
//        $this->debug();
    }
    
    /**
     * @Then the form should not contain an error
     */
    public function theFormShouldNotContainAnError()
    {
        $this->iShouldNotSeeTheRegion('form-errors');
//        $this->debug();
    }
    
    
    /**
     * @Then the following fields should have an error:
     */
    public function theFollowingFieldsOnlyShouldHaveAnError(TableNode $table)
    {
        $fields = array_keys($table->getRowsHash());
        $errorRegionCss = self::behatRegionToCssSelector('form-errors');
        $errorRegions = $this->getSession()->getPage()->findAll('css', $errorRegionCss);
        $foundIdsWithErrors = [];
        foreach ($errorRegions as $errorRegion) {
            $elementsWithErros = $errorRegion->findAll('xpath', "//*[name()='input' or name()='textarea' or name()='select']");
            foreach ($elementsWithErros as $elementWithError) { /* @var $found \Behat\Mink\Element\NodeElement */
                $foundIdsWithErrors[] = $elementWithError->getAttribute('id');
            }
        }
        $untriggeredField = array_diff($fields, $foundIdsWithErrors);
        $unexpectedFields = array_diff($foundIdsWithErrors, $fields);
        
        if ($untriggeredField || $unexpectedFields) {
            $message = "The form raised errors different than expected: \n";
            if ($untriggeredField) {
                $message .= " - Fields not throwing error as expected: " . implode(', ', $untriggeredField) . "\n";
            }
            if ($unexpectedFields) {
                 $message .= " - Fields unexpectedly throwing errors: " . implode(', ', $unexpectedFields) . "\n";
            }
            
            throw new \RuntimeException($message);
        }
    }
    
    
     /**
     * @Then /^the following fields should have the corresponding values:$/
     */
    public function followingFieldsShouldHaveTheCorrespondingValues(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->assertFieldContains($field, $value);
        }
    }

}