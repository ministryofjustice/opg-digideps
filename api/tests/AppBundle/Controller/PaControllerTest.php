<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Service\CsvUploader;

use Tests\AppBundle\Service\OrgServiceTest;

class PaControllerTest extends AbstractTestController
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

    public function testAddBulkAuth()
    {
        $url = '/org/bulk-add';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testAddBulk()
    {
        // add
        $data = $this->assertJsonRequest('POST', '/org/bulk-add', [
            'data' => CsvUploader::compressData(
                [
                    ['Dep Type'=>23] + OrgServiceTest::$deputy1 + OrgServiceTest::$client1,
                    ['Dep Type'=>21] + OrgServiceTest::$deputy2 + OrgServiceTest::$client2
                ]
            ),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEmpty($data['errors'], implode(',', $data['errors']));
        $this->assertEmpty($data['warnings'], implode(',', $data['warnings']));
        $this->assertEquals('dep1@provider.com', $data['added']['pa_users'][0]);
        $this->assertEquals('dep2@provider.com', $data['added']['prof_users'][0]);
        $this->assertEquals('00001111', $data['added']['clients'][0]);
        $this->assertEquals('00001111-2014-12-16', $data['added']['reports'][0]);
    }
}
