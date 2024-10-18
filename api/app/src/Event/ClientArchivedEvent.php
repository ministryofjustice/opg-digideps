<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientArchivedEvent extends Event
{
    public const NAME = 'client.archived';

    public function __construct(
        private readonly Client $client, 
        private readonly User $currentUser, 
        private readonly string $trigger
    ) {
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }
}
