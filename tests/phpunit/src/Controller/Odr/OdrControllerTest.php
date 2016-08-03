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
            'employment_support_allowance_incapacity_benefit',
            'income_support_pension_guarantee_credit'
        ];

        // "yes"
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'state_benefits' => $st,
                'receive_state_pension' => 'yes',
                'receive_other_income' => 'yes',
                'expect_compensation' => 'yes',
                'one_off' => [ //similar to debts
                    ['type_id' => 'bequest_or_inheritance', 'amount'=>345],
                    ['type_id' => 'cash_gift_received', 'amount'=>34],
                ],
            ],
        ]);

        $q = http_build_query(['groups' => [
            'odr-income-state-benefits',
            'odr-income-pension',
            'odr-income-damages',
            'odr-income-one-off',
        ]]);
        //assert both groups (quick)
        $data = $this->assertJsonRequest('GET', $url.'?'.$q, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        $this->assertEquals('bequest_or_inheritance', $data['income_one_off'][0]['type_id']);
        $this->assertEquals('345.00', $data['income_one_off'][0]['amount']);

        $this->assertEquals('refunds', $data['income_one_off'][2]['type_id']);
        $this->assertEquals(null, $data['income_one_off'][2]['amount']);

        $this->assertEquals('yes', $data['receive_state_pension']);
        $this->assertEquals('yes', $data['receive_other_income']);
        $this->assertEquals('yes', $data['expect_compensation']);
    }

}
