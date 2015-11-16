<?php
namespace AppBundle\Controller;

class ContactControllerTest extends ControllerTestCase
{
    /** @test */
    public function listActionRedirectToAddIfNoContactsAndNotDue() {

        $this->restClient->shouldReceive('get')->withArgs(['report/1/contacts', 'Contact[]'])->andReturn([]);
        $this->report->shouldReceive('isDue')->andReturn(false);

        $this->frameworkBundleClient->request( "GET","/report/1/contacts");
        $response =  $this->frameworkBundleClient->getResponse();
        $this->assertEquals( "/report/1/contacts/add", $response->getTargetUrl());

    }


}
