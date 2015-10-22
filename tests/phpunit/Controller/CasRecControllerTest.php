<?php

namespace AppBundle\Controller;

use AppBundle\Service\Mailer\MailSenderMock;

class CasRecControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        self::$deputy2 = self::fixtures()->createUser();

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
    
    
    public function testAddBulkAuth()
    {
        $url =  '/casrec/bulk-add';
        
        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }
    
    public function testAddBulk()
    {
        MailSenderMock::resetessagesSent();
        
        $this->assertJsonRequest('POST', '/casrec/bulk-add', [
            'rawData' => $this->compress([
                [
                    'Case' => '11', 
                    'Surname'=>'R1', 
                    'Deputy No' => 'DN1', 
                    'Dep Surname'=>'R2', 
                    'Dep Postcode'=>'SW1'
                ],
                [
                    'Case' => '22', 
                    'Surname'=>'H1', 
                    'Deputy No' => 'DN2', 
                    'Dep Surname'=>'H2', 
                    'Dep Postcode'=>''
                ],
                
            ]),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin
        ]);
        
        $users = $this->fixtures()->clear()->getRepo('CasRec')->findBy([], ['id'=>'ASC']);
        
        $this->assertCount(2, $users);
        
        $this->assertEquals('11', $users[0]->getCaseNumber());
        $this->assertEquals('R1', $users[0]->getClientLastname());
        $this->assertEquals('DN1', $users[0]->getDeputyNo());
        $this->assertEquals('R2', $users[0]->getDeputySurname());
        $this->assertEquals('SW1',  $users[0]->getDeputyPostCode());
        
        $this->assertEquals('22', $users[1]->getCaseNumber());
        $this->assertEquals('H1', $users[1]->getClientLastname());
        $this->assertEquals('DN2', $users[1]->getDeputyNo());
        $this->assertEquals('H2', $users[1]->getDeputySurname());
        $this->assertEquals('',  $users[1]->getDeputyPostCode());
    }
    
    
    public function testCountAuth()
    {
        $url = '/casrec/count';
        
        $this->assertEndpointNeedsAuth('GET', $url);
    }
    
    public function testCountAllAcl()
    {
        $url = '/casrec/count';
        
         $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
    }
    
    /**
     * @depends testAddBulk
     */
    public function testCountAll()
    {
        $url = '/casrec/count';
        
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin
        ])['data'];

        $this->assertEquals(2, $data);
    }
    
}
