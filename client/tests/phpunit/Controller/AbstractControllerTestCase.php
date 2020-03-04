<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'unittest', 'debug' => false]);
    }
}
