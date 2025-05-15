<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipPersister
{
    /**
     * The $builderResult should contain all of the entities to be persisted, and should all be related to
     * the same court order UID. If we are creating a new court order, we insert that first, so we have an ID;
     * we then directly add to the court_order_deputy and court_order_report tables to avoid having to load
     * the whole court order and its associated collections (which may be large).
     *
     * @return iterable<DeputyshipPersisterResult>
     */
    public function persist(DeputyshipBuilderResult $builderResult): iterable
    {
        // TODO implement persisting entities
        yield new DeputyshipPersisterResult();
    }
}
