<?php

namespace Tests\AppBundle\Controller;

class ManageControllerTest extends AbstractTestController
{
    public function testAvailability()
    {
        $ret = $this->assertJsonRequest('GET', '/manage/availability', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(1, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('', $ret['errors']);
    }

    public function testElb()
    {
        $ret = $this->assertJsonRequest('GET', '/manage/elb', [
                'assertResponseCode' => 200,
            ])['data'];

        $this->assertEquals('ok', $ret);
    }
}
