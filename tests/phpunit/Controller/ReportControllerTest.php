<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\Mailer\MailSenderMock;

class ReportControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport(self::$client1);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        self::$client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport(self::$client2);

        self::fixtures()->flush()->clear();
    }


    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }


    public function testAddAuth()
    {
        $url = '/report';
        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }


    public function testAddAcl()
    {
        $url = '/report';
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'id' => self::$report2->getId()
        ]);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'client' => self::$client2->getId()
        ]);
    }

    private $fixedData = [
            'court_order_type' => 1,
            'start_date' => '2015-01-01',
            'end_date' => '2015-12-31',
        ];

    public function testAddPost()
    {
        $url = '/report';
        
        $reportId = $this->assertJsonRequest('POST', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
                'data' => ['client' => self::$client1->getId()] + $this->fixedData
        ])['data']['report'];

        self::fixtures()->clear();

        // assert account created with transactions
        $report = self::fixtures()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
        $this->assertEquals('2015-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-31', $report->getEndDate()->format('Y-m-d'));
    }
    
    public function testAddPut()
    {
        $url = '/report';
        
        $reportId = $this->assertJsonRequest('POST', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
                'data' => ['id' => self::$report1->getId()] + $this->fixedData
        ])['data']['report'];

        self::fixtures()->clear();

        // assert account created with transactions
        $report = self::fixtures()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
        $this->assertEquals('2015-01-01', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-31', $report->getEndDate()->format('Y-m-d'));
    }


    public function testfindByIdAuth()
    {
        $url = '/report/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }


    public function testfindByIdAcl()
    {
        $url2 = '/report/' . self::$report2->getId();

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }


    /**
     * @depends testAddPost
     * @depends testAddPut
     */
    public function testfindById()
    {
        $url = '/report/' . self::$report1->getId();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
                'mustSucceed' => true,
                'AuthToken' => self::$tokenDeputy,
            ])['data'];

        $this->assertEquals(self::$report1->getId(), $data['id']);
        $this->assertEquals(self::$client1->getId(), $data['client']);
        $this->assertEquals('2015-01-01', $data['start_date']);
        $this->assertEquals('2015-12-31', $data['end_date']);
    }

    public function testSubmitAuth()
    {
        $url = '/report/' . self::$report1->getId() . '/submit';
        
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }
    
    public function testSubmitAcl()
    {
        $url2 = '/report/' . self::$report2->getId() . '/submit';
        
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    public function testSubmit()
    {
        MailSenderMock::resetessagesSent();
        $this->assertEquals(false, self::$report1->getSubmitted());
        
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/submit';

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);
        $this->assertContains('submit_date', $response['message']);

        $response = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'submit_date' => '2015-12-30'
            ]
        ]);
        
        // assert account created with transactions
        $report = self::fixtures()->clear()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals(true, $report->getSubmitted());
        $this->assertEquals('2015-12-30', $report->getSubmitDate()->format('Y-m-d'));
        
        //assert email
        $this->assertCount(1, MailSenderMock::getMessagesSent()['mailer.transport.smtp.default']);
        $this->assertEquals(['deputy@example.org'=>'test'], MailSenderMock::getMessagesSent()['mailer.transport.smtp.default'][0]['to']);
        
    }

    public function testUpdateAuth()
    {
        $url = '/report/' . self::$report1->getId();
        
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);
    }
    
    public function testUpdateAcl()
    {
        $url2 = '/report/' . self::$report2->getId();
        
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testSubmit
     */
    public function testUpdate()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId;

        // assert get
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'start_date' => '2015-01-29',
                'end_date' =>  '2015-12-29',
                // TODO add 'cot_id' reviewed report_seen 
                // reason_for_no_contacts no_asset_to_add 
                // reason_for_no_decisions further_information
            ]
        ]);

        $report = self::fixtures()->clear()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
        $this->assertEquals('2015-01-29', $report->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2015-12-29', $report->getEndDate()->format('Y-m-d'));
    }

}