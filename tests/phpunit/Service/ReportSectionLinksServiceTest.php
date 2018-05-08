<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryStub as m;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionLinksServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportSectionsLinkService
     */
    protected $sut;


    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->router = m::mock(RouterInterface::class);
        $this->router->shouldReceive('generate')->withAnyArgs()->andReturnUsing(function ($a, $b) {
            return $a . '-' . print_r($b, true);
        });
        $this->report = m::mock(ReportInterface::class);
        $this->report->shouldReceive('getType')->andReturn('102');
        $this->report->shouldReceive('getId')->andReturn('1');
        $this->report->shouldReceive('hasSection')->andReturn(true);


        $this->sut = new ReportSectionsLinkService($this->router);
    }

    public function testgetSectionParams()
    {

        $actual = $this->sut->getSectionParams($this->report, 'contacts', -1);
        $this->assertEquals('decisions', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'contacts', 1);
        $this->assertEquals('visitsCare', $actual['section']);

    }


    public function tearDown()
    {
        m::close();
    }
}
