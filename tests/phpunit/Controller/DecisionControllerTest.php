<?php
namespace AppBundle\Controller;

class DecisionControllerTest extends ControllerTestCase
{
    
    /** @test */
    public function listActionRedirectToAddIfNoDecisionsAndNotDue() {
        
        $this->restClient->shouldReceive('get')->withArgs(['report/1/decisions', 'Decision[]'])->andReturn([]);
        
        $this->frameworkBundleClient->request( "GET","/report/1/decisions");
        $response =  $this->frameworkBundleClient->getResponse();
        $this->assertEquals( "/report/1/decisions/add", $response->getTargetUrl());
        
    }
    
}
