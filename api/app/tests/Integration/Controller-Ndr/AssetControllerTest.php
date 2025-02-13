<?php

namespace App\Tests\Unit\Controller\Ndr;

use App\Entity\Ndr\AssetOther;
use App\Entity\Ndr\AssetProperty;
use app\tests\Integration\Controller\AbstractTestController;

class AssetControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $ndr1;
    private static $asset1;
    private static $assetp1;
    private static $deputy2;
    private static $client2;
    private static $ndr2;
    private static $asset2;
    private static $tokenAdmin;
    private static $tokenDeputy;

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        // deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$ndr1 = self::fixtures()->createNdr(self::$client1);
        self::$asset1 = self::fixtures()->createNdrAsset('other', self::$ndr1, ['setTitle' => 'asset1']);
        self::$assetp1 = self::fixtures()->createNdrAsset('property', self::$ndr1, ['setAddress' => 'ha1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$ndr2 = self::fixtures()->createNdr(self::$client2);
        self::$asset2 = self::fixtures()->createNdrAsset('other', self::$ndr2, ['setTitle' => 'asset2']);

        self::fixtures()->flush()->clear();
    }

    public function testgetAssetsAuth()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/assets';

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetAssetsAcl()
    {
        $url2 = '/ndr/'.self::$ndr2->getId().'/assets';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetAssets()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/assets';

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        // order by id ASC (insert order)
        usort($data, function ($a, $b) {
            return $a['id'] > $b['id'] ? 1 : 0;
        });

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
        $url = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetOneByIdAcl()
    {
        $url2 = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetOneById()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset1->getId();

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
        $url = '/ndr/'.self::$ndr1->getId().'/asset';

        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    public function testPostAcl()
    {
        $url2 = '/ndr/'.self::$ndr2->getId().'/asset';

        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);
    }

    public function testPostOther()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/asset';

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

        $asset = self::fixtures()->getRepo('Ndr\Asset')->find($return['data']['id']);
        /* @var $asset AssetOther */
        $this->assertInstanceOf('App\Entity\Ndr\AssetOther', $asset);
        $this->assertEquals(123, $asset->getValue());
        $this->assertEquals('de', $asset->getDescription());
        $this->assertEquals('01/01/2015', $asset->getValuationDate()->format('m/d/Y'));
        $this->assertEquals(self::$ndr1->getId(), $asset->getNdr()->getId());
    }

    public function testPostProperty()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/asset';

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

        $asset = self::fixtures()->getRepo('Ndr\Asset')->find($return['data']['id']);
        /* @var $asset AssetProperty */

        $this->assertInstanceOf('App\Entity\Ndr\AssetProperty', $asset);
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
        $url = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset1->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }

    public function testDeleteAcl()
    {
        $url2 = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset2->getId();
        $url3 = '/ndr/'.self::$ndr2->getId().'/asset/'.self::$asset2->getId();

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
        $url = '/ndr/'.self::$ndr1->getId().'/asset/'.self::$asset1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $this->assertTrue(null === self::fixtures()->getRepo('Ndr\Asset')->find(self::$asset1->getId()));
    }
}
