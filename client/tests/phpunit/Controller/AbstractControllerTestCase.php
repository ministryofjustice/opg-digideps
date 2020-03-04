<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $frameworkBundleClient;

    public function setUp(): void
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'unittest', 'debug' => false]);
    }

   /**
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     *
     * @return Response
     * @dataProvider getRouteMap
    */
    protected function httpRequest($method, $uri, array $parameters = [], array $files = [], array $server = [])
    {
        $this->frameworkBundleClient->request($method, $uri, $parameters, $files, $server);

        return $this->frameworkBundleClient->getResponse();
    }
}
