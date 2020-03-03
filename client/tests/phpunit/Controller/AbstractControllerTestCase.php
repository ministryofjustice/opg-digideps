<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Container */
    protected $container;

    /** @var Controller */
    protected $sut;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel(['environment' => 'unittest']);
    }

    public function setUp(): void
    {
        $this->container = self::$kernel->getContainer();
    }

    public function tearDown(): void
    {
        // purposefully not calling parent class, which shuts down the kernel
    }

    public static function tearDownAfterClass(): void
    {
        self::ensureKernelShutdown();
        self::$kernel = null;
    }

    public function getRouteMap()
    {
        return [];
    }

    /**
     * @dataProvider getRouteMap
     */
    public function testRoutes(string $url, string $action, array $params = []): void
    {
        $client = self::createClient(['environment' => 'unittest']);
        $router = $client->getContainer()->get('router');
        $match = $router->match($url);

        self::assertEquals(get_class($this->sut) . '::' . $action, $match['_controller']);
        foreach ($params as $key => $expectedValue) {
            self::assertEquals($expectedValue, $match[$key]);
        }
    }
}
