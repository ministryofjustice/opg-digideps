<?php

namespace AppBundle\Controller;

class AssetControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $asset1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $asset2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$asset1 = self::fixtures()->createAsset(self::$report1, ['setTitle'=>'title1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);
        self::$asset2 = self::fixtures()->createAsset(self::$report2);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures 
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        
        self::fixtures()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }
    
    public function testgetOneByIdAuth()
    {
        $url = '/report/asset/' . self::$asset1->getId();
        
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }
    
    public function testgetOneByIdAcl()
    {
        $url2 = '/report/asset/' . self::$asset2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }
    
    public function testgetOneById()
    {
        $url = '/report/asset/' . self::$asset1->getId();
        
        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals(self::$asset1->getId(), $data['id']);
        $this->assertEquals(self::$asset1->getTitle(), $data['title']);
    }
    
    public function testgetAssetsAuth()
    {
        $url = '/report/'.self::$report1->getId().'/assets';
        
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }
    
    public function testgetAssetsAcl()
    {
        $url2 = '/report/'.self::$report2->getId().'/assets';
        
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }
     
    public function testgetAssets()
    {
        $url = '/report/'.self::$report1->getId().'/assets';
        
        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        
        $this->assertCount(1, $data);
        $this->assertEquals(self::$asset1->getId(), $data[0]['id']);
        $this->assertEquals(self::$asset1->getTitle(), $data[0]['title']);
    }
    
    
    public function testupsertAssetAuth()
    {
        $url = '/report/asset';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }
    
    /**
     * @depends testgetAssets
     */
    public function testupsertAssetAcl()
    {
        $url2 = '/report/asset';
        
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy, [
            'report_id'=> self::$report2->getId()
        ]); 
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy, [
            'id' => self::$asset2->getId()
        ]); 
    }
    
    public function testupsertAssetMissingParams()
    {
        $url = '/report/asset';
        
        // empty params
        $errorMessage = $this->assertJsonRequest('POST', $url, [
            'data' => [
                'report_id'=>self::$report1->getId()
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 400
        ])['message'];
        $this->assertContains('value', $errorMessage);
        $this->assertContains('title', $errorMessage);
        $this->assertContains('description', $errorMessage);
    }
    
     private $dataUpdate = [
        'description' => 'description-changed', 
        'value' => 123, 
        'title' => 'title-changed', 
        'valuation_date' => '2015-11-27'
    ];
    
    public function testupsertAssetPut()
    {
        $url = '/report/asset';
        
        $return = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
            'data'=> ['id'=>self::$asset1->getId()] + $this->dataUpdate
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $asset = self::fixtures()->getRepo('Asset')->find($return['data']['id']); /* @var $asset \AppBundle\Entity\Asset */
        $this->assertEquals('title-changed', $asset->getTitle());
        $this->assertEquals('description-changed', $asset->getDescription());
        $this->assertEquals(123, $asset->getValue());
        $this->assertEquals('2015-11-27', $asset->getValuationDate()->format('Y-m-d'));
        $this->assertEquals(self::$report1->getId(), $asset->getReport()->getId());
    }
    
    public function testupsertAssetPost()
    {
        $url = '/report/asset';
        
        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
            'data'=> ['report_id'=> self::$report1->getId()] + $this->dataUpdate
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        $asset = self::fixtures()->getRepo('Asset')->find($return['data']['id']); /* @var $asset \AppBundle\Entity\Asset */
        $this->assertEquals('title-changed', $asset->getTitle());
        $this->assertEquals('description-changed', $asset->getDescription());
        $this->assertEquals(123, $asset->getValue());
        $this->assertEquals('2015-11-27', $asset->getValuationDate()->format('Y-m-d'));
        $this->assertEquals(self::$report1->getId(), $asset->getReport()->getId());
    }
    
    public function testDeleteAssetAuth()
    {
        $url = '/report/asset/' . self::$asset1->getId();
        
        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);
    }
    
    public function testDeleteAssetAcl()
    {
        $url2 = '/report/asset/' . self::$asset2->getId();
        
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);
    }
    
    
    /**
     * Run this last to avoid corrupting the data
     * 
     * @depends testgetAssets
     */
    public function testDeleteAsset()
    {
        $url = '/report/asset/' . self::$asset1->getId();
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ]);
        
        $this->assertTrue(null === self::fixtures()->getRepo('Asset')->find(self::$asset1->getId()));
    }
    
    
    public function testAddAssetOther()
    {
        $data = $this->assertPostRequest('/report/upsert-asset', [
            'report' => self::$report->getId(),
            'type' => 'other',
            'description' => 'my asset description',
            'value' => 123.45,
            'title' => 'my asset title',
        ]);

        // assert db contains record
        $asset = $this->fixtures->getRepo('Asset')->find($data['id']); /* @var $asset AssetOther */
        $this->assertEquals('my asset description', $asset->getDescription());
        $this->assertEquals(123.45, $asset->getValue());

        return $asset->getId();
    }


    /**
     * @depends testAddAssetOther
     */
    public function testGetAssetOther($assetId)
    {
        $data = $this->assertGetRequest('/report/get-asset/' . $assetId);
        $this->assertTrue($data['id'] > 0);
        $this->assertEquals(123.45, $data['value']);
        $this->assertEquals('my asset title', $data['title']);
        $this->assertEquals('my asset description', $data['description']);
        $this->assertEquals('other', $data['type']);
    }


    public function testAddAssetProperty()
    {
        $data = $this->assertPostRequest('/report/upsert-asset', [
            'report' => self::$report->getId(),
            'type' => 'property',
            'occupants' => 'only me',
            'occupantsInfo' => 'myself',
            'owned' => 'partly',
            'ownedPercentage' => '51',
            'isSubjectToEquityRelease' => true,
            'hasMortgage' => true,
            'mortgageOutstandingAmount' => 187500,
            'hasCharges' => true,
            'isRentedOut' => true,
            'rentAgreementEndDate' => new \DateTime('2015-12-31'),
            'rentIncomeMonth' => 1200,
            'address' => 'london road',
            'address2' => 'gold house',
            'county' => 'London',
            'postcode' => 'SW1 H11',
            'value'=> 250000.50
        ]);

        // assert db contains record
        $asset = $this->fixtures->getRepo('Asset')->find($data['id']); /* @var $asset AssetProperty */
        $this->assertInstanceOf('AppBundle\Entity\AssetProperty', $asset);
        $this->assertEquals('only me', $asset->getOccupants());

        return $asset->getId();
    }


    /**
     * @depends testAddAssetProperty
     */
    public function testGetAssetProperty($assetId)
    {
        $data = $this->assertGetRequest('/report/get-asset/' . $assetId);
        $this->assertTrue($data['id'] > 0);

        $this->assertKeysArePresentWithTheFollowingValues([
            'occupants' => 'only me',
            'occupants_info' => 'myself',
            'owned' => 'partly',
            'owned_percentage' => 51,
            'is_subject_to_equity_release' => true,
            'has_mortgage' => true,
            'mortgage_outstanding_amount' => 187500,
            'has_charges' => true,
            'is_rented_out' => true,
            'rent_agreement_end_date' => '2015-12-31T00:00:00+0000',
            'rent_income_month' => 1200,
            'type' => 'property',
            'type' => 'property',
            'address' => 'london road',
            'address2' => 'gold house',
            'county' => 'London',
            'postcode' => 'SW1 H11',
            'value'=> 250000.50
        ], $data, true);
    }

    /**
     * @depends testGetAssetProperty
     * @depends testGetAssetOther
     */
    public function testGetReportWithASsets()
    {
        $data = $this->assertGetRequest('/report/get-assets/' . self::$report->getId());
        $this->assertCount(2, $data);
    }
    
}
