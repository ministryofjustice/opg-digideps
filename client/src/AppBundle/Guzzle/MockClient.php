<?php declare(strict_types = 1);

namespace AppBundle\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;

class MockClient extends Client
{
    /**
     * @var MockHandler
     */
    private $handler;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $config['handler'] = $this->handler = new MockHandler();
        parent::__construct($config);
    }

    public function append(...$responses)
    {
        $this->handler->append(...$responses);
    }
}
