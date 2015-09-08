<?php
namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;


class SafeguardingTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_safeguarding.html.twig';

    public function testReportContainsSafeguardingSection() 
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->AndReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->AndReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->AndReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->AndReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);
        
        $text = $crawler->filter('#safeguarding-section')->eq(0)->text();
        
        $this->assertContains('Section 4', $text);
        $this->assertContains('Safeguarding', $text);
        
    }

    public function testShowThatILiveWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->AndReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->AndReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->AndReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->AndReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $text = $crawler->filter('#safeguarding-section')->eq(0)->text();

        $this->assertContains('Do you live with the client?', $text);
        
        $this->assertCheckboxChecked('')
        
    }
    
    
    public function testDontShowIntervalAnswersIfLivingWithClient()
    {
        $safeguardingData = m::mock('AppBundle\Entity\Safeguarding')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDoYouLiveWithClient')->AndReturn('yes')
            ->shouldReceive('getDoesClientReceivePaidCare')->AndReturn('no')
            ->shouldReceive('getWhoIsDoingTheCaring')->AndReturn('I do all the care')
            ->shouldReceive('getDoesClientHaveACarePlan')->AndReturn('no')
            ->getMock();

        $crawler = $this->getCrawler($safeguardingData);

        $text = $crawler->filter('#safeguarding-section')->eq(0)->text();

        $this->assertContains('Section 4', $text);
        $this->assertContains('Safeguarding', $text);        
    }
    
    public function testShowIntervalAnswersIfLivingWithClient()
    {
        
    }
    
    public function testDontLiveWithClientVisitsEveryday()
    {
        
    }
    
    public function testDoneLiveWithClientVisistsEveryWeek()
    {
        
    }
    
    
    private function getCrawler($safeguarding)
    {
        $html = $this->twig->render($this->templateName, [
            'safeguarding' => $safeguarding
        ]);

        $crawler = new Crawler($html);
        
        return $crawler;
    }
    
    
    // Find a checkbox in the given container (css) in a form with a given name and a 
    // look for a checkbox with the label give and check it's state matches
    // e.g. assertCheckboxChecked('#safeguarding-section','Do you live with the client?', true)
    protected function assertCheckboxChecked($crawler, $container, $label, $checked)
    {
        $containerElement = $crawler.filter($container)->eq(0);
        
        $field = $containerElement.filterXPath("//*[text()='" . $label . "']");
        
        // now find the bit that has the checkbox
        
        $text = 'test';
        
        if ($checked == true) {
            assertContains('X', $text);
        } else {
            assertNotContains('X', $text);
        }
    }
    
}
