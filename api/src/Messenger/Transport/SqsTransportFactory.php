<?php

declare(strict_types=1);

namespace App\Messenger\Transport;

use AsyncAws\Sqs\SqsClient;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsReceiver;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsSender;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\Connection;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class SqsTransportFactory implements TransportFactoryInterface
{
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $client = new SqsClient(['endpoint' => $options['endpoint']]);
        $connection = new Connection($options, $client);
        $receiver = new AmazonSqsReceiver($connection, $serializer);
        $sender = new AmazonSqsSender($connection, $serializer);

        return new AmazonSqsTransport($connection, $serializer, $receiver, $sender);
    }

    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'sqs://');
    }
}
