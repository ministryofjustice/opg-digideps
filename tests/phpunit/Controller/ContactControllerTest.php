<?php

namespace AppBundle\Controller;

class ContactControllerTest extends AbstractControllerTestCase
{
    /** @test */
    public function listActionRedirectToAddIfNoContactsAndNotDue()
    {
        $this->report->shouldReceive('isDue')->andReturn(false);
        $this->report->shouldReceive('getContacts')->andReturn([]);

        $this->frameworkBundleClient->request('GET', '/report/1/contacts');
        $response = $this->frameworkBundleClient->getResponse();
        $this->assertEquals('/report/1/contacts/add', $response->getTargetUrl());
    }
}
