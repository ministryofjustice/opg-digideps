<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\AuditLogEntry;

class AuditLogControllerTest extends AbstractTestController
{
    public function setUp()
    {
        parent::setUp();

        $this->admin = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        $this->deputy = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $this->tokenDeputy = $this->loginAsDeputy();
        $this->tokenAdmin = $this->loginAsAdmin();
    }

    public function testaddAction()
    {
        // assert auth
        $this->assertEndpointNeedsAuth('POST', '/audit-log');

        $validData = [
            'performed_by_user' => [
                'id' => $this->admin->getId(),
            ],
            'ip_address' => '1.2.3.4',
            'created_at' => '2015-05-20',
            'action' => 'login',
            'user_edited' => [
                'id' => $this->deputy->getId(),
            ],
        ];

        // assert deputy not allowed
        $this->assertJsonRequest('POST', '/audit-log', [
            'data' => $validData,
            'mustFail' => true,
            'AuthToken' => $this->tokenDeputy,
            'assertResponseCode' => 403,
        ]);

        // assert missing params
        $errorMessage = $this->assertJsonRequest('POST', '/audit-log', [
            'data' => [],
            'mustSucceed' => false,
            'AuthToken' => $this->tokenAdmin,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertContains("Missing 'performed_by_user'", $errorMessage);
        $this->assertContains("Missing 'ip_address'", $errorMessage);
        $this->assertContains("Missing 'created_at'", $errorMessage);

        // assert successful POST really creates the entry
        $return = $this->assertJsonRequest('POST', '/audit-log', [
            'data' => $validData,
            'mustSucceed' => true,
            'AuthToken' => $this->tokenAdmin,
        ]);

        $entries = self::fixtures()->clear()->getRepo('AuditLogEntry')->findAll();
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
        $this->assertEndpointNeedsAuth('GET', '/audit-log');

        // assert deputy cannot access endpoint
        $this->assertJsonRequest('GET', '/audit-log', [
            'mustFail' => true,
            'AuthToken' => $this->tokenDeputy,
            'assertResponseCode' => 403,
        ]);

        // assert endpoint returns expected data including previously created audit log entry
        $return = $this->assertJsonRequest('GET', '/audit-log', [
            'mustSucceed' => true,
            'AuthToken' => $this->tokenAdmin,
        ])['data'];

        $this->assertEquals($entryId, $return[0]['id']);
        $this->assertEquals($this->admin->getId(), $return[0]['performed_by_user']['id']);
        $this->assertEquals($this->deputy->getId(), $return[0]['user_edited']['id']);
        $this->assertEquals('login', $return[0]['action']);
    }
}
