<?php

declare(strict_types=1);

namespace App\Messenger\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

class SqsTransport implements TransportInterface
{
    public function __construct()
    {
    }

    public function get(): iterable
    {
        // TODO: Implement get() method.
    }

    public function ack(Envelope $envelope): void
    {
        // TODO: Implement ack() method.
    }

    public function reject(Envelope $envelope): void
    {
        // TODO: Implement reject() method.
    }

    public function send(Envelope $envelope): Envelope
    {
        // TODO: Implement send() method.
    }
}
