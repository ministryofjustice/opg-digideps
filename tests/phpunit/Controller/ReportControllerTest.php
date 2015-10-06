<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;

class ReportControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $client1;
    private static $report1;
    private static $deputy2;
    private static $client2;
    private static $report2;
    private static $tokenAdmin;
    private static $tokenDeputy;
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname'=>'c1']);
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
        #if (null === self::$tokenAdmin) {
        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy();
        #}
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
            'id'=> self::$report2->getId()
        ]); 
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy, [
            'client' => self::$client2->getId()
        ]); 
    }
    
    public function testAdd()
    {
       $url = '/report';
        
       foreach([
           ['client' => self::$client1->getId()],
           ['id' => self::$report1->getId()],
       ] as $data) {
        $reportId = $this->assertRequest('POST', $url, [
             'mustSucceed'=>true,
             'AuthToken' => self::$tokenDeputy,
             'data'=> $data + [
                 'court_order_type' => 1,
                 'start_date' => '2015-01-01',
                 'end_date' =>  '2015-12-31',
                 
             ]
         ])['data']['report'];

         self::fixtures()->clear();

         // assert account created with transactions
         $report = self::fixtures()->getRepo('Report')->find($reportId); /* @var $report \AppBundle\Entity\Report */
         $this->assertEquals(self::$client1->getId(), $report->getClient()->getId());
         $this->assertEquals('2015-01-01', $report->getStartDate()->format('Y-m-d'));
         $this->assertEquals('2015-12-31', $report->getEndDate()->format('Y-m-d'));

       }
    }
    
    public function testfindByIdAuth()
    {
        $url = '/report/find-by-id/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url); 
        
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin); 
    }

    public function testfindByIdAcl()
    {
        $url2 = '/report/find-by-id/' . self::$report2->getId();
        
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy); 
    }
    
    /**
     * @depends testAdd
     */
    public function testfindById()
    {
        $url = '/report/find-by-id/' . self::$report1->getId();
        
          // assert get
        $data = $this->assertRequest('GET', $url,[
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        
        $this->assertEquals(self::$report1->getId(), $data['id']);
        $this->assertEquals(self::$client1->getId(), $data['client']);
        $this->assertEquals('2015-01-01', $data['start_date']);
        $this->assertEquals('2015-12-31', $data['end_date']);
    }
}

