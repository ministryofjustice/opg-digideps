<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class ContactControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static Contact $contact1;
    private static Report $report2;
    private static Contact $contact2;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$contact1 = self::fixtures()->createContact(self::$report1, ['setAddress' => 'address1']);
        self::$contact2 = self::fixtures()->createContact(self::$report2);

        self::fixtures()->flush()->clear();

        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy($user1->getEmail());
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    private static array $dataUpdate = [
        'contact_name' => 'contact_name-changed',
        'address' => 'address-changed',
        'address2' => 'address2-changed',
        'county' => 'county-changed',
        'postcode' => 'SW1',
        'country' => 'UK',
        'explanation' => 'explanation-changed',
        'relationship' => 'relationship-changed',
    ];

    public function testGetOneByIdAuth(): void
    {
        $url = '/report/contact/' . self::$contact1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl(): void
    {
        $url2 = '/report/contact/' . self::$contact2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetOneById(): void
    {
        $url = '/report/contact/' . self::$contact1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$contact1->getId(), $data['id']);
        $this->assertEquals(self::$contact1->getAddress(), $data['address']);
    }

    public function testGetContactsAuth(): void
    {
        $url = '/report/' . self::$report1->getId() . '/contacts';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetContactsAcl(): void
    {
        $url2 = '/report/' . self::$report2->getId() . '/contacts';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetContacts(): void
    {
        $url = '/report/' . self::$report1->getId() . '/contacts';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertCount(1, $data);
        $this->assertEquals(self::$contact1->getId(), $data[0]['id']);
        $this->assertEquals(self::$contact1->getAddress(), $data[0]['address']);
    }

    public function testUpsertContactAuth(): void
    {
        $url = '/report/contact';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    /**
     * @depends testGetContacts
     */
    public function testUpsertContactAcl(): void
    {
        $url2 = '/report/contact';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy, [
            'report_id' => self::$report2->getId(),
        ]);
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy, [
            'id' => self::$contact2->getId(),
        ]);
    }

    public function testUpsertContactMissingParams(): void
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

    public function testUpsertContactPut(): void
    {
        $url = '/report/contact';

        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['id' => self::$contact1->getId()] + self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $contact = self::fixtures()->getRepo(Contact::class)->find($return['data']['id']);
        $this->assertEquals('address-changed', $contact->getAddress());
        $this->assertEquals(self::$report1->getId(), $contact->getReport()->getId());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_CONTACTS));
    }

    public function testUpsertContactPost(): void
    {
        $url = '/report/contact';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => ['report_id' => self::$report1->getId()] + self::$dataUpdate,
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $contact = self::fixtures()->getRepo(Contact::class)->find($return['data']['id']);
        $this->assertEquals('address-changed', $contact->getAddress());
        $this->assertEquals(self::$report1->getId(), $contact->getReport()->getId());
        // TODO assert other fields
    }

    public function testDeleteContactAuth(): void
    {
        $url = '/report/contact/' . self::$contact1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteContactAcl(): void
    {
        $url2 = '/report/contact/' . self::$contact2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testGetContacts
     */
    public function testDeleteContact(): void
    {
        $url = '/report/contact/' . self::$contact1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(self::fixtures()->getRepo(Contact::class)->find(self::$contact1->getId()) === null);

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_CONTACTS));
    }
}
