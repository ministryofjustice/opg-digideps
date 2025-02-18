<?php

namespace App\Tests\Integration\Service\BruteForce;

// create a simple predis Mock to just return keys
class PredisMock extends \Predis\Client
{
    private $data;
    private $time;

    public function __construct()
    {
        $this->time = 0;
    }

    public function set($id, $value)
    {
        $this->data[$id] = $value;
    }

    public function get($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : null;
    }

    public function expire($id, $timeout)
    {
        if (!isset($this->data[$id])) {
            throw new \InvalidArgumentException("key $id not set");
        }
    }

    public function __call($commandID, $arguments)
    {
        throw new \InvalidArgumentException("PredisMock: Method $commandID not implemented");
    }
}
