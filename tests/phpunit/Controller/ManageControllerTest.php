<?php

namespace AppBundle\Controller;

class ManageControllerTest extends AbstractControllerTestCase
{
    public function testAvailability()
    {
        $this->markTestIncomplete('use $this->frameworkBundleClient');
        $container = $this->getClient()->getContainer();

        $t1 = $container->get('mailer.transport.smtp.default')->resetMockVars();
        $t2 = $container->get('mailer.transport.smtp.secure')->resetMockVars();

        $ret = $this->assertJsonRequest('GET', '/manage/availability', [
            'assertResponseCode' => 200,
        ])['data'];

        $this->assertEquals(1, $ret['healthy'], print_r($ret, true));
        $this->assertEquals('', $ret['errors']);

        $this->assertTrue($t1->isStarted());
        $this->assertTrue($t2->isStarted());
    }

    public function testElb()
    {
        $this->markTestIncomplete('use $this->frameworkBundleClient');
        $ret = $this->assertJsonRequest('GET', '/manage/elb', [
                'assertResponseCode' => 200,
            ])['data'];

        $this->assertEquals('ok', $ret);
    }
}
