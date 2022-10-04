<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DocumentsSynchronisedEvent extends Event
{
    public const NAME = 'documents.synchronised';

    private array $documents;

    public function __construct(array $documents)
    {
        $this->setDocuments($documents);
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function setDocuments(array $documents): DocumentsSynchronisedEvent
    {
        $this->documents = $documents;

        return $this;
    }
}
