<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

final class CourtOrderRelationshipChanges
{
    /**
     * @var array<CourtOrderRelationshipChange>
     */
    private array $changes;

    public function __construct()
    {
        $this->changes = [];
    }

    public function add(CourtOrderRelationshipChange $courtOrderRelationship): void
    {
        $this->changes[] = $courtOrderRelationship;
    }

    /**
     * @return \Generator<int, CourtOrderRelationshipChange, void, void>
     */
    public function drain(): \Generator
    {
        while (!empty($this->changes)) {
            yield array_pop($this->changes);
        }
    }
}
