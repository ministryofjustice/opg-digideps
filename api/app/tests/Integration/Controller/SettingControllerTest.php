<?php

namespace App\Tests\Unit\Controller;

use App\Entity\Setting;
use App\Tests\Unit\Fixtures;

class SettingControllerTest extends AbstractTestController
{
    // users
    private static $tokenDeputy;
    private static $tokenAdmin;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenAdmin = $this->loginAsAdmin();
        }
    }

    public function testgetOneByIdNotPresent()
    {
        Fixtures::deleteReportsData(['setting']);

        $id = 'service-notification';
        $url = '/setting/'.$id;

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals([], $data);
    }

    public function testgetOneByIdPresent()
    {
        $id = 'service-notification';
        $url = '/setting/'.$id;
        Fixtures::deleteReportsData(['setting']);

        $setting = new Setting($id, 'snc', true);
        self::fixtures()->persist($setting)->flush();

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
        ])['data'];

        $this->assertEquals($id, $data['id']);
        $this->assertEquals('snc', $data['content']);
        $this->assertEquals(true, $data['enabled']);
    }

    public function testupdate()
    {
        $id = 'service-notification';
        $url = '/setting/'.$id;
        $settingContent = 'snc1'.implode(' ', range(1, 1000));
        Fixtures::deleteReportsData(['setting']);

        // assert Auth
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenDeputy);

        // assert PUT (first time / add)
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'content' => $settingContent,
                'enabled' => true,
            ],
        ]);
        $setting = self::fixtures()->clear()->getRepo('Setting')->find($id);
        /* @var $setting Setting */
        $this->assertEquals($id, $setting->getId());
        $this->assertEquals($settingContent, $setting->getContent());
        $this->assertEquals(true, $setting->isEnabled());

        // assert PUT (first time / add)
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
            'data' => [
                'content' => 'snc2',
                'enabled' => false,
            ],
        ]);
        $setting = self::fixtures()->clear()->getRepo('Setting')->find($id);
        /* @var $setting Setting */
        $this->assertEquals($id, $setting->getId());
        $this->assertEquals('snc2', $setting->getContent());
        $this->assertEquals(false, $setting->isEnabled());
    }
}
