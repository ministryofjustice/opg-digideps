<?php

namespace App\Tests\Unit\ControllerReport;

use App\Entity\Report\Report;
use App\Tests\Unit\Controller\AbstractTestController;

class ContactControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $contact1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $contact2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$contact1 = self::fixtures()->createContact(self::$report1, ['setAddress' => 'address1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$contact2 = self::fixtures()->createContact(self::$report2);

        self::fixtures()->flush()->clear();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    private $dataUpdate = [
        'contact_name' => 'contact_name-changed',
        'address' => 'address-changed',
        'address2' => 'address2-changed',
        'county' => 'county-changed',
        'postcode' => 'SW1',
        'country' => 'UK',
        'explanation' => 'explanation-changed',
        'relationship' => 'relationship-changed',
    ];

    public function testgetOneByIdAuth()
    {
        $url = '/report/contact/'.self::$contact1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/report/contact/'.self::$contact2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/report/contact/'.self::$contact1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$contact1->getId(), $data['id']);
        $this->assertEquals(self::$contact1->getAddress(), $data['address']);
    }

    public function testgetContactsAuth()
    {
        $url = '/report/'.self::$report1->getId().'/contacts';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetContactsAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/contacts';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetContacts()
    {
        $url = '/report/'.self::$report1->getId().'/contacts';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(1, $data);
        $this->assertEquals(self::$contact1->getId(), $data[0]['id']);
        $this->assertEquals(self::$contact1->getAddress(), $data[0]['address']);
    }

    public function testupsertContactAuth()
    {
        $url = '/report/contact';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    /**
     * @depends testgetContacts
     */
    public function testupsertContactAcl()
    {
        $url2 = '/report/contact';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy, [
            'report_id' => self::$report2->getId(),
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy, [
            'id' => self::$contact2->getId(),
        ]);
    }

    public function testupsertContactMissingParams()
    {
        $url = '/report/contact';

        // empty params
        $errorMessage = $this->assertJsonRequest('POST', $url, [
            'data' => [
                'report_id' => self::$report1->getId(),
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400,
        ])['message'];
        $this->assertStringContainsString('contact_name', $errorMessage);
        $this->assertStringContainsString('address', $errorMessage);
        $this->assertStringContainsString('address2', $errorMessage);
        $this->assertStringContainsString('county', $errorMessage);
        $this->assertStringContainsString('postcode', $errorMessage);
        $this->assertStringContainsString('country', $errorMessage);
        $this->assertStringContainsString('explanation', $errorMessage);
        $this->assertStringContainsString('relationship', $errorMessage);
    }

    public function testupsertContactPut()
    {
        $url = '/report/contact';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$contact1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $contact = self::fixtures()->getRepo('Report\Contact')->find($return['data']['id']); /* @var $contact \App\Entity\Report\Contact */
        $this->assertEquals('address-changed', $contact->getAddress());
        $this->assertEquals(self::$report1->getId(), $contact->getReport()->getId());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_CONTACTS));
    }

    public function testupsertContactPost()
    {
        $url = '/report/contact';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + $this->dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $contact = self::fixtures()->getRepo('Report\Contact')->find($return['data']['id']); /* @var $contact \App\Entity\Report\Contact */
        $this->assertEquals('address-changed', $contact->getAddress());
        $this->assertEquals(self::$report1->getId(), $contact->getReport()->getId());
        // TODO assert other fields
    }

    public function testDeleteContactAuth()
    {
        $url = '/report/contact/'.self::$contact1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteContactAcl()
    {
        $url2 = '/report/contact/'.self::$contact2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testgetContacts
     */
    public function testDeleteContact()
    {
        $url = '/report/contact/'.self::$contact1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Report\Contact')->find(self::$contact1->getId()));

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_CONTACTS));
    }
}
