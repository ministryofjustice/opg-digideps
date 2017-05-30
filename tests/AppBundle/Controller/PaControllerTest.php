<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\pa;
use Fixtures;
use Tests\AppBundle\Service\PaServiceTest;

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
        $url = '/pa/bulk-add';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testAddBulk()
    {
        $postData['chunkSize'] = 2;
        $postData['compressedData'] = $this->compress([PaServiceTest::$deputy1 + PaServiceTest::$client1]);
        $postData['line'] = 0;

        // add
        $data = $this->assertJsonRequest('POST', '/pa/bulk-add', [
            'data' => $postData,
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals('dep1@provider.com', $data['added']['users'][0]);
        $this->assertEquals('10000001', $data['added']['clients'][0]);
        $this->assertEquals('10000001-2014-12-16', $data['added']['reports'][0]);
    }

    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }
}
