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
    use RegionTrait, 
        DebugTrait,
        StatusSnapshotTrait,
        LinksTrait,
        SiteNavigationTrait,
        AuthenticationTrait,
        EmailTrait,
        FormTrait,
        ReportTrait,
        KernelDictionary,
        ExpressionTrait,
        UserTrait;
    
    private static $dbName;
    
    private static $autoDbSnapshot;
    
    public function __construct($options = [])
    {
        //$options['session']; // not used
        $maxNestingLevel = isset($options['maxNestingLevel']) ? $options['maxNestingLevel'] : 200;
        ini_set('xdebug.max_nesting_level', $maxNestingLevel);
        ini_set('max_nesting_level', $maxNestingLevel);
        $this->sessionName = empty($options['sessionName']) ? 'digideps' : $options['sessionName'];
        self::$dbName = empty($options['dbName']) ? 'api' : $options['dbName'];
        self::$autoDbSnapshot = isset($options['autoDbSnapshot']) ? $options['autoDbSnapshot'] : false;
    }
        
    
    public function setKernel(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    protected function getSymfonyParam($name)
    {
        return $this->getContainer()->getParameter($name);
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
     * @Then the page title should be :text
     */
    public function thePageTitleShouldBe($text)
    {
        $this->iShouldSeeInTheRegion($text, 'page-title');
    }
   
    
    /**
     * @Then the response should have the :arg1 header containing :arg2
     */
    public function theResponseShouldHaveTheHeaderContaining($header, $value)
    {
        $headers = $this->getSession()->getDriver()->getResponseHeaders();
        if (empty($headers[$header][0])) {
            throw new \Exception("Header '{$header}' not found.");
        }
        if (strpos($headers[$header][0], $value) === false) {
            throw new \Exception("Header '{$header}' has value '{$headers[$header][0]}' that does not contains '{$value}'");
        }
        
    }
    
    /**
     * @Given The response header :header should contain :value
     */
    public function theResponseHeaderShouldContain($header, $value)
    {
        $responseHeaders = $this->getSession()->getDriver()->getResponseHeaders();
        
        if (!isset($responseHeaders[$header])) {
             throw new \Exception("Header $header not found in response. Headers found: " . implode(', ', array_keys($responseHeaders)));
        }
        
        // search in 
        $found = false;
        foreach ((array)$responseHeaders[$header] as $currentValue) {
            if (strpos($currentValue, $value) !== false) {
                $found = true;
            }
        }
        if (!$found) {
            throw new \Exception("Header $header not found in response. Values: " . implode(', ', $responseHeaders[$header]));
        }
    }
    
    /**
     * @Then the last audit log entry should contain:
     */
    public function theLastAuditLogEntryShouldContain(TableNode $fields)
    {
        $this->visitBehatLink('view-audit-log');
        
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->iShouldSeeInTheRegion($value, 'entry-1');
        }
        
    }
    
    /**
     * @Given the application config is valid
     */
    public function iChecktheAppParameterFile()
    {
        $this->visitBehatLink("check-app-params");
        $this->assertResponseStatus(200);
    }
    
    /**
     * @Given I confirm the report is ready to be submitted
     */
    public function IconfirmTheReportIsReadyToBeSubmitted()
    {
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', "a#edit-report_add_further_info");
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("a#edit-report_add_further_info not found. Report does not seem ready to be submitted");
        }
    }
   
}
