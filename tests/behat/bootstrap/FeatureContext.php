<?php

namespace DigidepsBehat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

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

    private static $fixtures = null;

    public function __construct($session)
    {
        ini_set('xdebug.max_nesting_level', 200);
    }

        /**
     * This class is constructed at each scenario
     * 
     * @return ApplicationBehatHelper
     */
    public static function getApplicationBehatHelper()
    {
        if (null === self::$fixtures) {
            $appConf = array(
                'modules' => array(
                    'Application',
                    'DoctrineModule',
                    'DoctrineORMModule',
                ),
                'module_listener_options' => array(
                    'module_paths' => array(
                        './module',
                        './vendor'
                    ),
                    'config_glob_paths' => array(
                        'config/autoload/{,*.}{global,behat}.php'
                    )
                )
            );

            $application = Zend\Mvc\Application::init($appConf);
            $sm = $application->getServiceManager();

            $migrationConf = $sm->get('doctrine.migrationsconfiguration.ormdefault');
            $result = array_diff($migrationConf->getMigratedVersions(), $migrationConf->getAvailableVersions());
            if (!empty($result)) {
                throw new \RuntimeException('Database not updated ! run migrations first');
            }

            self::$fixtures = new ApplicationBehatHelper($sm);
        }

        return self::$fixtures;
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
        $this->fillField('email',$email);
        $this->fillField('password', $password);
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
        $this->getSession()->getPage()->pressButton('submitbutton');
    }
    
    /**
     * @When I change the client court order type to :value
     */
    public function fillClientCourtOrderType($value)
    {
        $courtOrderTypeNameToId = array_flip(Application\Entity\CourtOrderType::getCourtOrderTypesArray());
        if (empty($courtOrderTypeNameToId[$value])) {
            throw new \Exception("Cannot find Court order type $value in the dropdown. Check 'CourtOrderType::getCourtOrderTypesArray()'");
        }
        
        $this->getSession()->getPage()->fillField('court_order_type_id', $courtOrderTypeNameToId[$value]);
    }
    
    /**
     * @Then the page title should be :text
     */
    public function thePageTitleShouldBe($text)
    {
        $this->iShouldSeeInTheRegion($text, 'page-title');
    }
    
    
    /**
     * @Then an email with subject :subject should have been sent to :to
     */
    public function anEmailWithSubjectShouldHaveBeenSentTo($subject, $to)
    {
        $mail = self::getApplicationBehatHelper()->getLatestEmail();

        if ($mail->getSubject() != $subject) {
            throw new \RuntimeException("Subject '" . $mail->getSubject() . "' does not match the expected '" . $subject . "'");
        }
        if ($to !== 'the specified email address' && $mail->getTo() != $to) {
            throw new \RuntimeException("Addressee '" . $mail->getTo() . "' does not match the expected '" . $to . "'");
        }
    }
    
    /**
     * @When an email containing the following should have been sent to :to:
     */
    public function anEmailContainingTheFollowingShouldHaveBeenSentTo2($to, TableNode $table)
    {
        $mail = self::getApplicationBehatHelper()->getLatestEmail();
        
        if ($to !== 'the specified email address' && $mail->getTo() != $to) {
            throw new \RuntimeException("Addressee '" . $mail->getTo() . "' does not match the expected '" . $to . "'");
        }
        
        foreach (array_keys($table->getRowsHash()) as $search) {
            if (strpos($mail->getRaw(), $search) === false) {
                throw new \RuntimeException("Email body does not contain the string '$search'");
            }
        }
        
    }

    /**
     * @BeforeScenario @cleanMail
     */
    public function cleanMail(BeforeScenarioScope $scope)
    {
        self::getApplicationBehatHelper()->cleanLatestEmail();
    }

    /**
     * @When I open the first link on the email
     */
    public function iOpenTheLinkOnTheEmail()
    {
        $mailContent = self::getApplicationBehatHelper()->getLatestEmail()->getRaw();
        preg_match_all('#http://[^\s"<]+#', $mailContent, $matches);
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
     * @Then the email sent has a size of at least :atLeastKb kilobytes
     */
    public function theEmailSentHasASizeBiggerThan($atLeastKb) //7
    {
        $mailSize = ceil(strlen(self::getApplicationBehatHelper()->getLatestEmail()->getRaw()) / 1024); //73
        if ($mailSize < $atLeastKb) {
            throw new \Exception("mail size is $mailSize bytes, should be bigger than $atLeastKb kb");
        }
    }
    
    /**
     * Delete users with an email starting with behat-
     * RElies on region "test-user" added in the admin view
     * @Given I delete the behat test users
     */
    public function iDeleteTheBehatUsers()
    {
        $this->iGoToTheManageUsersPage();
        
        $regions = $this->getSession()->getPage()->findAll('css', self::behatRegionToCssSelector('test-user'));
        foreach ($regions as $region) {
            $editLinks = $region->findAll('css', self::behatLinkToCssSelector('edit'));
            if (count($editLinks) !== 1) { //it only happened once, could not replicate
                $this->debug();
            }
            $editLinks[0]->click();
            $this->clickOnBehatLink('delete-user');
            $this->clickOnBehatLink('delete-yes');
        }
        
        $this->iShouldNotSeeTheRegion('test-user');
    }


}