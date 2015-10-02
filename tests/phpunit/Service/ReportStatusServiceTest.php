<?php
namespace AppBundle\Tests\Service;

use AppBundle\Service\ReportStatusService;
use Mockery as m;
use AppBundle\Entity as EntityDir;
use Symfony\Component\Translation\TranslatorInterface;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase {

    // Decisions 
    
    /** @test */
    public function indicateSingleDecision() {

        $decisions = array(1);
        
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisions')->andReturn($decisions)
            ->getMock();
        
        $translator = m::mock('Symfony\Component\Translation\TranslatorInterface')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('trans')->andReturn("Decision")
            ->getMock();
        
        $reportStatusService = new ReportStatusService($report, $translator);
        
        $answer = $reportStatusService->getDecisionsStatus();
        
        $this->assertEquals("1 Decision", $answer);
        
    }
    
    /** @test */
    public function indicateMultipleDecisions() {
        
    }
    
    /** @test */
    public function indicateNoDecisionsMade() {
        
    }
    
    /** @test */
    public function indicateDecisionsNotStarted() {
        
    }
    
    
    
    
}
