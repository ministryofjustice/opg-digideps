<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\Report\Report;
use App\Tests\Integration\Controller\AbstractTestController;

class AssetControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $asset1;
    private static $assetp1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $asset2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$asset1 = self::fixtures()->createAsset('other', self::$report1, ['setTitle' => 'asset1']);
        self::$assetp1 = self::fixtures()->createAsset('property', self::$report1, ['setAddress' => 'ha1']);
        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$asset2 = self::fixtures()->createAsset('other', self::$report2, ['setTitle' => 'asset2']);

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

    public function testgetAssets()
    {
        $data = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['asset']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data']['assets'];

        $this->assertCount(2, $data);

        $this->assertEquals(self::$asset1->getId(), $data[0]['id']);
        $this->assertEquals('asset1', $data[0]['title']);
        $this->assertEquals('other', $data[0]['type']);

        $this->assertEquals(self::$assetp1->getId(), $data[1]['id']);
        $this->assertEquals('ha1', $data[1]['address']);
        $this->assertEquals('property', $data[1]['type']);
    }

    public function testgetOneByIdAuth()
    {
        $url = '/report/'.self::$report1->getId().'/asset/'.self::$asset1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/report/'.self::$report1->getId().'/asset/'.self::$asset2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/report/'.self::$report1->getId().'/asset/'.self::$asset1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$asset1->getId(), $data['id']);
        $this->assertEquals(self::$asset1->getTitle(), $data['title']);
    }

    public function testPostAuth()
    {
        $url = '/report/'.self::$report1->getId().'/asset';

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    public function testPostAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/asset';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);
    }

    public function testPostOther()
    {
        $url = '/report/'.self::$report1->getId().'/asset';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'type' => 'other',
                'value' => 123,
                'description' => 'de',
                'valuation_date' => '01/01/2015',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $asset = self::fixtures()->getRepo('Report\Asset')->find($return['data']['id']); /* @var $asset \App\Entity\Report\AssetOther */
        $this->assertInstanceOf('App\Entity\Report\AssetOther', $asset);
        $this->assertEquals(123, $asset->getValue());
        $this->assertEquals('de', $asset->getDescription());
        $this->assertEquals('01/01/2015', $asset->getValuationDate()->format('m/d/Y'));
        $this->assertEquals(self::$report1->getId(), $asset->getReport()->getId());

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_ASSETS));
    }

    public function testPostProperty()
    {
        $url = '/report/'.self::$report1->getId().'/asset';

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'type' => 'property',
                'occupants' => 'me',
                'owned' => 'partly',
                'owned_percentage' => '51',
                'is_subject_to_equity_release' => true,
                'has_mortgage' => true,
                'mortgage_outstanding_amount' => 187500,
                'has_charges' => true,
                'is_rented_out' => true,
                'rent_agreement_end_date' => '2015-12-31',
                'rent_income_month' => 1200,
                'address' => 'london road',
                'address2' => 'gold house',
                'county' => 'London',
                'postcode' => 'SW1 H11',
                'value' => 250000.50,
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $asset = self::fixtures()->getRepo('Report\Asset')->find($return['data']['id']); /* @var $asset \App\Entity\Report\AssetProperty */

        $this->assertInstanceOf('App\Entity\Report\AssetProperty', $asset);
        $this->assertEquals('me', $asset->getOccupants());
        $this->assertEquals('partly', $asset->getOwned());
        $this->assertEquals('51.00', $asset->getOwnedPercentage());
        $this->assertEquals(true, $asset->getIsSubjectToEquityRelease());
        $this->assertEquals(true, $asset->getHasMortgage());
        $this->assertEquals(187500, $asset->getMortgageOutstandingAmount());
        $this->assertEquals(true, $asset->getHasCharges());
        $this->assertEquals(true, $asset->getIsRentedOut());
        $this->assertEquals('12/31/2015', $asset->getRentAgreementEndDate()->format('m/d/Y'));
        $this->assertEquals(1200, $asset->getRentIncomeMonth());
        $this->assertEquals('london road', $asset->getAddress());
        $this->assertEquals('gold house', $asset->getAddress2());
        $this->assertEquals('London', $asset->getCounty());
        $this->assertEquals('SW1 H11', $asset->getPostcode());
        $this->assertEquals(250000.50, $asset->getValue());
    }

    public function testDeleteAuth()
    {
        $url = '/report/'.self::$report1->getId().'/asset/'.self::$asset1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/report/'.self::$report1->getId().'/asset/'.self::$asset2->getId();
        $url3 = '/report/'.self::$report2->getId().'/asset/'.self::$asset2->getId();

        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
        $this->assertEndpointNotAllowedFor('DELETE', $url3, self::$tokenDeputy);
    }

    /**
     * Run this last to avoid corrupting the data.
     *
     * @depends testgetAssets
     */
    public function testDelete()
    {
        $url = '/report/'.self::$report1->getId().'/asset/'.self::$asset1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Report\Asset')->find(self::$asset1->getId()));

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_ASSETS));
    }
}
