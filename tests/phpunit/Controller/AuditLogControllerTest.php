<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AuditLogEntry;

class AuditLogControllerTest extends AbstractTestController
{
    public function setUp()
    {
        parent::setUp();
        
        $this->admin = $this->fixtures->getRepo('User')->findOneByEmail('admin@example.org');
        $this->deputy = $this->fixtures->getRepo('User')->findOneByEmail('deputy@example.org');
    }


    public function testaddAction()
    {
        $this->assertEndpointReturnAuthError('POST', '/audit-log');
        
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $errorMessage = $this->assertRequest('POST', '/audit-log', [
            'data' => [
            ],
            'mustSucceed' => false,
            'AuthToken' => $token,
            'assertResponseCode' => 400
        ])['message'];
        $this->assertContains("Missing 'performed_by_user'", $errorMessage);
        $this->assertContains("Missing 'ip_address'", $errorMessage);
        $this->assertContains("Missing 'created_at'", $errorMessage);
        
        $return = $this->assertRequest('POST', '/audit-log', [
            'data' => [
                'performed_by_user' => [
                    'id' => $this->admin->getId()
                ], 
                'ip_address' => '1.2.3.4', 
                'created_at' => '2015-05-20', 
                'action' => 'login',
                'user_edited' => [
                    'id' => $this->deputy->getId()
                ]
            ],
            'mustSucceed' => true,
            'AuthToken' => $token
        ]);
        
        $entries = $this->fixtures->clear()->getRepo('AuditLogEntry')->findAll();
        $this->assertCount(1, $entries);
        $entry = array_shift($entries); /* @var $entry AuditLogEntry */
        $this->assertEquals('login', $entry->getAction());
        $this->assertEquals('2015-05-20', $entry->getCreatedAt()->format('Y-m-d'));
        $this->assertEquals('1.2.3.4', $entry->getIpAddress());
        $this->assertEquals($this->admin->getId(), $entry->getPerformedByUser()->getId());
        $this->assertEquals($this->deputy->getId(), $entry->getUserEdited()->getId());
        
        return $return['data']['id'];
    }
    
    /**
     * @depends testaddAction
     */
    public function testgetAll($entryId)
    {
        $this->assertEndpointReturnAuthError('GET', '/audit-log');
        
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $return = $this->assertRequest('GET', '/audit-log', [
            'mustSucceed' => true,
            'AuthToken' => $token
        ])['data'];
        
        $entry = $return[0];
        $this->assertEquals($entryId, $entry['id']);
        $this->assertEquals($this->admin->getId(), $entry['performed_by_user']['id']);
        $this->assertEquals($this->deputy->getId(), $entry['user_edited']['id']);
        $this->assertEquals('login', $entry['action']);
        
    }
    
    
}
