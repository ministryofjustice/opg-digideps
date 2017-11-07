<?php
//
//namespace Tests\AppBundle\Controller;
//
//use AppBundle\Entity\Report\Report;
//
//class StatsControllerTest extends AbstractTestController
//{
//    private static $deputy1;
//    private static $admin1;
//    private static $tokenAdmin = null;
//    private static $tokenDeputy = null;
//    private static $client1;
//    private static $report1;
//    private static $report2;
//    private static $report3;
//    private static $report4;
//    private static $account1;
//    private static $account2;
//
//    public static function setUpBeforeClass()
//    {
//        parent::setUpBeforeClass();
//
//        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
//        self::$deputy1->setRegistrationDate(new \DateTime('2015-10-15'));
//
//        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
//
//        self::$client1 = self::fixtures()->createClient(self::$deputy1, [
//            'setFirstname' => 'c1',
//            'setLastName' => 'l1',
//            'setCourtDate' => new \DateTime('2016-12-30'),
//            'setCaseNumber' => '222333t',
//        ]);
//
//        // report 1
//        self::$report1 = self::fixtures()->createReport(self::$client1, ['setEndDate' => new \DateTime('yesterday')]);
//
//        // report2 (submitted)
//        self::$report2 = self::fixtures()->createReport(self::$client1, ['setSubmitted'=>true, 'setSubmitDate'=>new \DateTime('01-01-2014')]);
//        self::$report2 = self::fixtures()->createReport(self::$client1, ['setSubmitted'=>true, 'setSubmitDate'=>new \DateTime('01-01-2015')]);
//        self::$report2 = self::fixtures()->createReport(self::$client1, ['setSubmitted'=>true, 'setSubmitDate'=>new \DateTime('01-01-2016')]);
//
//        self::fixtures()->flush();
//        self::fixtures()->clear();
//    }
//
//    public function setUp()
//    {
//        if (null === self::$tokenAdmin) {
//            self::$tokenAdmin = $this->loginAsAdmin();
//            self::$tokenDeputy = $this->loginAsDeputy();
//        }
//    }
//
//
//
//    public function testGetStatsCsv()
//    {
//        $url = '/stats/stats.csv';
//
//        self::$frameworkBundleClient->request('GET',$url, [], [], ['HTTP_AuthToken'=>'WRONG']);
//        $this->assertEquals(419, self::$frameworkBundleClient->getResponse()->getStatusCode());
//
//        self::$frameworkBundleClient->request('GET',$url, [], [], ['HTTP_AuthToken'=>self::$tokenDeputy]);
//        $this->assertEquals(403, self::$frameworkBundleClient->getResponse()->getStatusCode());
//
//        self::$frameworkBundleClient->request('GET',$url, [], [], ['HTTP_AuthToken'=>self::$tokenAdmin]);
//        echo self::$frameworkBundleClient->getResponse()->getContent();
//
//
////        echo $data;
//
////        $deputy = array_filter($data, function ($user) {
////            return $user['email'] == 'deputy@example.org';
////        });
////        $deputy = array_shift($deputy);
////        $this->assertEquals(self::$deputy1->getId(), $deputy['id']);
////        $this->assertEquals('test', $deputy['name']);
////        $this->assertEquals('deputy', $deputy['lastname']);
////        $this->assertEquals('2016-12-30', $deputy['client_court_order_date']);
////        $this->assertEquals('2015-10-15', $deputy['registration_date']);
////        $this->assertEquals('2016-01-01', $deputy['report_date_submitted']);
////        $this->assertContains(date('Y-m-d'), $deputy['last_logged_in']);
////        $this->assertEquals('c1', $deputy['client_name']);
////        $this->assertEquals('l1', $deputy['client_lastname']);
////        $this->assertEquals('222333t', $deputy['client_casenumber']);
////        $this->assertEquals(4, $deputy['total_reports']);
////        $this->assertEquals(1, $deputy['active_reports']);
//    }
//
//
////
////    public function testGetAllWithStats()
////    {
////        $url = '/stats/users';
//////        $this->assertEndpointNeedsAuth('GET', $url);
//////        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
////
////        $this->fixtures()->deleteReportsData(['casrec']);
////
////        \Fixtures::deleteReportsData(['casrec']);
////        $this->fixtures()->persist($this->c1, $this->c2)->flush($this->c1, $this->c2);
////
////        // check count
////
////        $records = $this->assertJsonRequest('GET', $url, [
////            'mustSucceed' => true,
////            'AuthToken' => self::$tokenAdmin,
////        ])['data']; /* @var $records CasRec[] */
////
////        $this->assertCount(2, $records);
////        $this->assertEquals('12345678', $records[0]['Case']);
////        $this->assertEquals('c1', $records[0]['custom']);
////        $this->assertEquals('c1', $records[0]['custom 2']);
////        //check stats
////        $this->assertContains(date('d/m/Y'), $records[0]['Uploaded at']);
////        $this->assertEquals('-', $records[0]['Stats updated at']);
////        $this->assertEquals('n.a.', $records[0]['Deputy registration date']);
////        $this->assertEquals('n.a.', $records[0]['Deputy last logged in']);
////        $this->assertEquals('n.a.', $records[0]['Reports submitted']);
////        $this->assertEquals('n.a.', $records[0]['Reports active']);
////
////
////        $this->assertEquals('12345679', $records[1]['Case']);
////        $this->assertEquals('c2', $records[1]['custom']);
////        $this->assertEquals('', $records[1]['custom 2']);
////
////    }
//}
