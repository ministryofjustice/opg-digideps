<?php

namespace AppBundle\Controller;

use AppBundle\Service\Mailer\MailSenderMock;

class StatsControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;
    private static $client1;
    private static $report1;
    private static $report2;
    private static $account1;
    private static $account2;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);
        self::fixtures()->flush();


        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1);
        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank'=>'bank1']);

        // report2
        self::$report2 = self::fixtures()->createReport(self::$client1)->setSubmitted(true);

        self::fixtures()->flush();

        self::fixtures()->getConnection()->query('UPDATE account_transaction SET amount=1 WHERE id < 10')->execute();

        self::fixtures()->clear();
    }
    
    /**
     * clear fixtures 
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->getConnection()->query('DELETE FROM account_transaction WHERE account_id = '.self::$account1->getId())->execute();

        self::fixtures()->clear();
    }


    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }
    
    
    public function testStatsUsersAuth()
    {
        $url = '/stats/users';
        
        $this->assertEndpointNeedsAuth('GET', $url);

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
    }


    
    public function testStatsUsers()
    {
        $url = '/stats/users';
        
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin
        ])['data'];

        $first = array_shift($data);
        $this->assertArrayHasKey('is_active', $first);
        $this->assertArrayHasKey('email', $first);
        $this->assertArrayHasKey('total_reports', $first);
        $this->assertArrayHasKey('active_reports', $first);
        $this->assertArrayHasKey('active_reports_due', $first);
        $this->assertArrayHasKey('active_reports_added_bank_accounts', $first);
        $this->assertArrayHasKey('active_reports_added_transactions', $first);
    }

}
