<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\BruteForce;

use Predis\Client;
use InvalidArgumentException;

// create a simple predis Mock to just return keys
class PredisMock extends Client
{
    private $data;

    public function __construct()
    {
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function expire($key, $seconds): void
    {
        if (!isset($this->data[$key])) {
            throw new InvalidArgumentException("key $key not set");
        }
    }

    public function __call($commandID, $arguments)
    {
        throw new InvalidArgumentException("PredisMock: Method $commandID not implemented");
    }
}
