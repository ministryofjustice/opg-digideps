<?php

namespace App\Tests\Unit\Controller;

class HealthControllerTest extends AbstractTestController
{
    public function testServiceHealth()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check/service', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(1, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('', $ret['errors']);
    }

    public function testContainerHealth()
    {
        $ret = $this->assertJsonRequest('GET', '/health-check', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals('ok', $ret);
    }
}
