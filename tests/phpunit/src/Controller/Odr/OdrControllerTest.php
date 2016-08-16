<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractTestController;
use AppBundle\Entity\Odr\VisitsCare;

class OdrControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $odr1;
    private static $deputy2;
    private static $client2;
    private static $odr2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::$odr1 = self::fixtures()->createOdr(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$odr2 = self::fixtures()->createOdr(self::$client2);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
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

    public function testGetOneByIdAuth()
    {
        $url = '/odr/'.self::$odr1->getId();

        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetOneByIdAcl()
    {
        $url2 = '/odr/'.self::$odr2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }


    public function testGetOneByIdData()
    {
        $url = '/odr/'.self::$odr1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(self::$odr1->getId(), $data['id']);


        // assert debts
        $data = $this->assertJsonRequest('GET', $url.'?groups=odr-debt', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertArrayHasKey('debts', $data);

    }

    public function testSubmitAuth()
    {
        $url = '/odr/'.self::$odr1->getId().'/submit';

        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }

    public function testSubmitAcl()
    {
        $url2 = '/odr/'.self::$odr2->getId().'/submit';

        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testSubmit()
    {
        $this->assertEquals(false, self::$odr1->getSubmitted());

        $odrId = self::$odr1->getId();
        $url = '/odr/'.$odrId.'/submit';

        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-31',
            ],
        ]);

        // assert account created with transactions
        $odr = self::fixtures()->clear()->getRepo('Odr\Odr')->find($odrId);
        /* @var $odr \AppBundle\Entity\Odr\Odr */
        $this->assertEquals(true, $odr->getSubmitted());
        $this->assertEquals('2015-12-31', $odr->getSubmitDate()->format('Y-m-d'));
    }

    public function testDebts()
    {
        $url = '/odr/'.self::$odr1->getId();

        // "yes"
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_debts' => 'yes',
                'debts' => [
                    ['debt_type_id' => 'care-fees', 'amount'=>1, 'more_details'=> 'should not be saved'],
                    ['debt_type_id' => 'credit-cards', 'amount'=>2, 'more_details'=> ''],
                    ['debt_type_id' => 'loans', 'amount'=>3, 'more_details'=> ''],
                    ['debt_type_id' => 'other', 'amount'=>4, 'more_details'=> 'md'],
                ]
            ],
        ]);

        $q = http_build_query(['groups' => ['odr-debt']]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $debt = array_shift($data['debts']);
        $this->assertEquals('care-fees', $debt['debt_type_id']);
        $this->assertEquals(1, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('credit-cards', $debt['debt_type_id']);
        $this->assertEquals(2.00, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('loans', $debt['debt_type_id']);
        $this->assertEquals(3.00, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $debt = array_shift($data['debts']);
        $this->assertEquals('other', $debt['debt_type_id']);
        $this->assertEquals(4.00, $debt['amount']);
        $this->assertEquals('md', $debt['more_details']);
        $this->assertEquals(10, $data['debts_total_amount']);
        $this->assertEquals('yes', $data['has_debts']);

        // "no"
        self::fixtures()->flush()->clear();
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'has_debts' => 'no',
                'debts' => []
            ],
        ]);
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $debt = array_shift($data['debts']);
        $this->assertEquals('care-fees', $debt['debt_type_id']);
        $this->assertEquals(0, $debt['amount']);
        $this->assertEquals('', $debt['more_details']);
        $this->assertEquals(0, $data['debts_total_amount']);
        $this->assertEquals('no', $data['has_debts']);
    }

    public function testIncomeBenefits()
    {
        $url = '/odr/'.self::$odr1->getId();

        $st = [
            ['type_id'=>'contributions_based_allowance', 'present'=>true, 'more_details'=>null],
            ['type_id'=>'income_support_pension_guarantee_credit', 'present'=>false, 'more_details'=>null],
            ['type_id'=>'other_benefits', 'present'=>true, 'more_details'=>'obmd'],
        ];

        $oo = [
            ['type_id'=>'bequest_or_inheritance', 'present'=>true, 'more_details'=>null],
            ['type_id'=>'sale_of_an_asset', 'present'=>false, 'more_details'=>null],
        ];

        // PUT
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'state_benefits' => $st,
                'receive_state_pension' => 'no',
                'receive_other_income' => 'yes',
                'receive_other_income_details' => 'roid',
                'expect_compensation_damages' => 'yes',
                'expect_compensation_damages_details' => 'exdd',
                'one_off' => $oo,
            ],
        ]);

        // GET and assert
        $q = http_build_query(['groups' => [
            'odr-income-benefits',
            'odr-income-state-benefits',
            'odr-income-pension',
            'odr-income-damages',
            'odr-income-one-off'
        ]]);
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        // assert state benefits
        $this->assertEquals([
            'id' => 1,
            'type_id' => 'contributions_based_allowance',
            'present' => true,
            'has_more_details' => false,
            'more_details' => null,
        ], $data['state_benefits'][0]);

        $this->assertEquals([
            'id' => 2,
            'type_id' => 'income_support_pension_guarantee_credit',
            'present' => false,
            'has_more_details' => false,
            'more_details' => null,
        ], $data['state_benefits'][1]);

        $this->assertEquals([
            'id' => 12,
            'type_id' => 'other_benefits',
            'present' => true,
            'has_more_details' => true,
            'more_details' => 'obmd',
        ], $data['state_benefits'][11]);

        // assert income and damages (Odr properties)
        $this->assertEquals('no', $data['receive_state_pension']);
        $this->assertEquals('yes', $data['receive_other_income']);
        $this->assertEquals('roid', $data['receive_other_income_details']);
        $this->assertEquals('yes', $data['expect_compensation_damages']);
        $this->assertEquals('exdd', $data['expect_compensation_damages_details']);

        // assert one-off
        $this->assertEquals([
            'id' => 1,
            'type_id' => 'bequest_or_inheritance',
            'present' => true,
            'has_more_details' => false,
            'more_details' => null,
        ], $data['one_off'][0]);

        $this->assertEquals([
            'id' => 2,
            'type_id' => 'cash_gift_received',
            'present' => false,
            'has_more_details' => false,
            'more_details' => null,
        ], $data['one_off'][1]);

    }

}
