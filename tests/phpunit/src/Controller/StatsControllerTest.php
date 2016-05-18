<?php

namespace AppBundle\Controller;

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

        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1, ['setEndDate' => new \DateTime('yesterday')]);
        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank1']);
        self::fixtures()->createTransaction(self::$report1, 'rent'.microtime(1), [1]);
        self::fixtures()->createTransaction(self::$report1, 'mortgage'.microtime(1), [2]);
        self::fixtures()->createTransaction(self::$report1, 'salary'.microtime(1), [3]);

        // report2
        self::$report2 = self::fixtures()->createReport(self::$client1, [])->setSubmitted(true);

        self::fixtures()->flush();
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
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $first = array_shift($data);
        $this->assertArrayHasKey('is_active', $first);
        $this->assertArrayHasKey('email', $first);
        $this->assertArrayHasKey('total_reports', $first);
        $this->assertArrayHasKey('active_reports', $first);
        //assert using "GreaterThanOrEqual" in case something else was added from previous tests
        $this->assertGreaterThanOrEqual(1, $first['active_reports_due']);
        $this->assertGreaterThanOrEqual(1, $first['active_reports_added_bank_accounts']);
        $this->assertGreaterThanOrEqual(3, $first['active_reports_added_transactions']);
    }
}
