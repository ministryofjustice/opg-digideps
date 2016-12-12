<?php

namespace Tests\AppBundle\Controller;

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
        self::$deputy1->setRegistrationDate(new \DateTime('2015-10-15'));

        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');

        self::$client1 = self::fixtures()->createClient(self::$deputy1, [
            'setFirstname' => 'c1',
            'setLastName' => 'l1',
            'setCourtDate' => new \DateTime('2016-12-30'),
            'setCaseNumber' => '222333t',
        ]);

        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1, ['setEndDate' => new \DateTime('yesterday')]);

        // report2 (submitted)
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
        $this->assertEquals(self::$client1->getId(), $first['id']);
        $this->assertEquals('test', $first['name']);
        $this->assertEquals('deputy', $first['lastname']);
        $this->assertEquals('2016-12-30', $first['client_court_order_date']);
        $this->assertEquals('2015-10-15', $first['registration_date']);
        $this->assertContains(date('Y-m-d'), $first['last_logged_in']);
        $this->assertEquals('c1', $first['client_name']);
        $this->assertEquals('l1', $first['client_lastname']);
        $this->assertEquals('222333t', $first['client_casenumber']);
        $this->assertEquals(2, $first['total_reports']);
        $this->assertEquals(1, $first['active_reports']);
    }
}
