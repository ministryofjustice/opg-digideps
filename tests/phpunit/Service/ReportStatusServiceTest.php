<?php
namespace AppBundle\Service;

use AppBundle\Service\ReportStatusService;
use Mockery as m;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Entity\Account;
use Symfony\Component\Translation\TranslatorInterface;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase {

    /** @var \Mockery\MockInterface $translator */
    private $translator;
    
    public function setUp() {
        $this->markTestSkipped('deprecated');
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('trans')->with('decision',[], 'status')->andReturn("Decision")
            ->shouldReceive('trans')->with('decisions',[], 'status')->andReturn("Decisions")
            ->shouldReceive('trans')->with('nodecisions',[], 'status')->andReturn("No decisions")
            ->shouldReceive('trans')->with('contact',[], 'status')->andReturn("Contact")
            ->shouldReceive('trans')->with('contacts',[], 'status')->andReturn("Contacts")
            ->shouldReceive('trans')->with('account',[], 'status')->andReturn("Account")
            ->shouldReceive('trans')->with('accounts',[], 'status')->andReturn("Accounts")
            ->shouldReceive('trans')->with('asset',[], 'status')->andReturn("Asset")
            ->shouldReceive('trans')->with('assets',[], 'status')->andReturn("Assets")
            ->shouldReceive('trans')->with('noassets',[], 'status')->andReturn("No assets")
            ->shouldReceive('trans')->with('nocontacts',[], 'status')->andReturn("No contacts")
            ->shouldReceive('trans')->with('notstarted',[], 'status')->andReturn("Not started")
            ->shouldReceive('trans')->with('finished',[], 'status')->andReturn("Finished")
            ->getMock();

    }

    public function tearDown() {
        m::close();
    }

    
    /** @test */
    public function hasOutstandingAccountsIsTrue()
    {
        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(false)
            ->getMock();

        $accounts = array($account);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();
        
        $reportStatusService = new ReportStatusService($report, $this->translator);
        
        $this->assertTrue($reportStatusService->hasOutstandingAccounts());
    }

    /** @test */
    public function hasOutstandingAccountsIsFalse()
    {
        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $accounts = array($account);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $this->assertFalse($reportStatusService->hasOutstandingAccounts());
    }

    
    /** @test */
    public function isReadyToSubmitIsFalseMissingContacts()
    {
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();
        
        $y = [];
        $z = count($y);
        $e = empty($y);
        
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getReasonForNoContacts')->andReturn('')
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertFalse($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsFalseMissingDecisions()
    {
        $contact = m::mock('AppBundle\Entity\Contact');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->isReadyToSubmit();
        $this->assertFalse($answer);
    }

    /** @test */
    public function isReadyToSubmitIsFalseMissingAssets()
    {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        
        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([])
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertFalse($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsFalseMissingSafeguarding()
    {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(true)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertFalse($reportStatusService->isReadyToSubmit());
    }
    
    /** @test */
    public function isReadyToSubmitIsFalseNoAccounts()
    {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertFalse($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsFalseAccountIncomplete()
    {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(false)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertFalse($reportStatusService->isReadyToSubmit());
    }
    
    /** @test */
    public function isReadyToSubmitIsTrue() {
        
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);
        
        
        $this->assertTrue($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsTrueNoContacts() {

        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn('stuff')
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertTrue($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsTrueNoAssets() {
        $decision = m::mock('AppBundle\Entity\Decision');
        $contact = m::mock('AppBundle\Entity\Contact');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getNoAssetsToAdd')->andReturn(true)
            ->shouldReceive('getAssets')->andReturn([])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertTrue($reportStatusService->isReadyToSubmit());
    }

    /** @test */
    public function isReadyToSubmitIsTrueNoDecisions() {

        $contact = m::mock('AppBundle\Entity\Contact');

        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn("stuff")
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);


        $this->assertTrue($reportStatusService->isReadyToSubmit());
    }
    
    /** @test */
    public function indicateSingleDecision() {

        $decisions = array(1);
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();
        
        $reportStatusService = new ReportStatusService($report, $this->translator);
        
        $answer = $reportStatusService->getDecisionsStatus();
        
        $this->assertEquals("1 Decision", $answer);
        
    }
    
    /** @test */
    public function indicateMultipleDecisions() {
        $decisions = array(1,2);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsStatus();

        $this->assertEquals("2 Decisions", $answer); 
    }
    
    /** @test */
    public function indicateNoDecisionsMade() {
        $decisions = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("There was nothing to decide")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsStatus();

        $this->assertEquals("No decisions", $answer);
    }
    
    /** @test */
    public function indicateDecisionsNotStarted() {
        $decisions = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsStatus();

        $this->assertEquals("Not started", $answer);
    }
    
    /** @test */
    public function indicateSingleContact() {

        $contacts = array(1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsStatus();

        $this->assertEquals("1 Contact", $answer);

    }

    /** @test */
    public function indicateMultipleContacts() {
        $contacts = array(1,2);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsStatus();

        $this->assertEquals("2 Contacts", $answer);
    }

    /** @test */
    public function indicateNoContactsAdded() {
        $contacts = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("There was nothing")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsStatus();

        $this->assertEquals("No contacts", $answer);
    }

    /** @test */
    public function indicateContactsNotStarted() {
        $contacts = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsStatus();

        $this->assertEquals("Not started", $answer);
    }

    /** @test */
    public function indicateThatSafeguardingHasNotBeenStarted() {
        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();
        
        $reportStatusService = new ReportStatusService($report, $this->translator);
        $answer = $reportStatusService->getSafeguardingStatus();

        $this->assertEquals("Not started", $answer);       
    }
    
    /** @test */
    public function indicateThatSafeguardingIsComplete() {
        
        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getSafeguardingStatus();

        $this->assertEquals("Finished", $answer);
    }

    /** @test */
    public function indicateAccountsNotStarted() {
        $accounts = array();
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAccountsStatus();

        $this->assertEquals("Not started", $answer);
    }

    /** @test */
    public function indicateAccountsAdded() {
        $accounts = array(1,2);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAccountsStatus();

        $this->assertEquals("2 Accounts", $answer);
    }    
    
    /** @test */
    public function indicateSingleAssetStatus() {
        $assets = array(1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsStatus();

        $this->assertEquals("1 Asset", $answer);
    }

    /** @test */
    public function indicateMultipleAssetsStatus() {
        $assets = array(1,1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsStatus();

        $this->assertEquals("2 Assets", $answer);
    }

    /** @test */
    public function indicateWhenNoAssetsToAdd() {
        $assets = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(true)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsStatus();

        $this->assertEquals("No assets", $answer);
    }

    /** @test */
    public function indicateAssetsNotStarted() {
        $assets = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsStatus();

        $this->assertEquals("Not started", $answer);
    }
    
    /** @test */
    public function indicateDecisionsStateNotStarted() {
        $decisions = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsState();

        $this->assertEquals(ReportStatusService::NOTSTARTED, $answer);
    }

    /** @test */
    public function indicateDecisionsStateDoneWhenDecisions() {
        $decisions = array(1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }

    /** @test */
    public function indicateDecisionsStateDoneWhenIndicatedNone() {
        $decisions = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("stuff")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getDecisionsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }

    /** @test */
    public function indicateContactsStateNotStarted() {
        $contacts = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsState();

        $this->assertEquals(ReportStatusService::NOTSTARTED, $answer);
    }

    /** @test */
    public function indicateContactsStateDoneWithContacts() {
        $contacts = array(1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }

    /** @test */
    public function indicateContactsStateDoneForReason() {
        $contacts = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getContacts')->andReturn($contacts)
            ->shouldReceive('getReasonForNoContacts')->andReturn("stuff")
            ->getMock();

        /** @var ReportStatusService $reportStatusService */
        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getContactsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }
    
    /** @test */
    public function indicateThatSafeguardingStateHasNotBeenStarted() {
        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);
        $answer = $reportStatusService->getSafeguardingState();

        $this->assertEquals(ReportStatusService::NOTSTARTED, $answer);
    }

    /** @test */
    public function indicateThatSafeguardingStateIsComplete() {

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->getMock();


        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getSafeguardingState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }

    /** @test */
    public function indicateAccountsStateNotStarted() {
        $accounts = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAccountsState();

        $this->assertEquals(ReportStatusService::NOTSTARTED, $answer);
    }
    
    /** @test */
    public function indicateAccountsStateInProgress() {
        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(false)
            ->getMock();
        
        $accounts = array($account);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAccountsState();

        $this->assertEquals(ReportStatusService::INCOMPLETE, $answer);    
    }
    
    /** @test */
    public function indicateAccountsStateDone() {
        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $accounts = array($account);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAccounts')->andReturn($accounts)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAccountsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);
    }
    
    /** @test */
    public function indicateAssetsNotStartedState() {
        $assets = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsState();

        $this->assertEquals(ReportStatusService::NOTSTARTED, $answer);
    }
    
    /** @test */
    public function indicateAssetsDoneWithAssets() {
        $assets = array(1);

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);       
    }
    
    /** @test */
    public function indicateAssetsDoneWithoutAssets() {
        $assets = array();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAssets')->andReturn($assets)
            ->shouldReceive('getNoAssetToAdd')->andReturn(true)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getAssetsState();

        $this->assertEquals(ReportStatusService::DONE, $answer);        
    }
 
    
    /** @test */
    public function statusSectionsCompleteNotDue() {

        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getStatus();
        $expected = "notFinished";
        $this->assertEquals($expected, $answer);
    }
    
    /** @test */
    public function statusSectionsNotCompleteNotDue() {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([])
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getStatus();
        $expected = "notFinished";
        $this->assertEquals($expected, $answer);
    }
    
    /** @test */
    public function statusSectionsNotCompleteIsDue() {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([])
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getStatus();
        $expected = "notFinished";
        $this->assertEquals($expected, $answer);
    }
    
    /** @test */
    public function statusSectionsCompleteIsDue() {
        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        $asset = m::mock('AppBundle\Entity\Asset');

        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();

        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([$asset])
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);

        $answer = $reportStatusService->getStatus();
        $expected = "readyToSubmit";
        $this->assertEquals($expected, $answer);

    }
    
    /** @test */
    public function calculateStatusRemainingCount() {

        $contact = m::mock('AppBundle\Entity\Contact');
        $decision = m::mock('AppBundle\Entity\Decision');
        
        $safeguarding = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('missingSafeguardingInfo')->andReturn(false)
            ->getMock();

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('hasClosingBalance')->andReturn(true)
            ->getMock();
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->shouldReceive('getAssets')->andReturn([])
            ->shouldReceive('getNoAssetToAdd')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getSafeguarding')->andReturn($safeguarding)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $reportStatusService = new ReportStatusService($report, $this->translator);
        
        $this->assertEquals(1, $reportStatusService->getRemainingSectionCount());
        
    }
    
}
