<?php

namespace AppBundle\Twig;



class AssetsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AssetsExtension */
    private $assetsExtension;

    public function setup()
    {
        $this->clean();
        mkdir('/tmp/test/app', 0777, true);
        mkdir('/tmp/test/web/assets/1234', 0777, true);

        $this->assetsExtension = new AssetsExtension('/tmp/test/app');
    }

    public function tearDown()
    {
        $this->clean();
    }

    private function clean()
    {
        if (file_exists('/tmp/test/app')) {
            rmdir('/tmp/test/app');
        }
        if (file_exists('/tmp/test/web/assets/1234')) {
            rmdir('/tmp/test/web/assets/1234');
        }
        if (file_exists('/tmp/test/web/assets/1234')) {
            rmdir('/tmp/test/web/assets');
        }
        if (file_exists('/tmp/test/web/assets/1234')) {
            rmdir('/tmp/test/web');
        }
        if (file_exists('/tmp/test/web/assets/1234')) {
            rmdir('/tmp/test');
        }
    }

    /** @test */
    public function getSimpleJavascriptUrl()
    {
        $answer = $this->assetsExtension->assetUrlFilter('javascripts/test.js');
        $this->assertEquals('/assets/1234/javascripts/test.js', $answer);
    }
}
