<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipCandidateAction;

/**
 * A sorted group of StagingDeputyshipCandidates, represented as arrays, which can be converted into appropriate
 * database actions. This class is responsible for grouping and de-duplicating candidates, and for returning them
 * in a safe order to be applied to the database.
 *
 * The iterator returns array representations of candidates.
 *
 * @implements \IteratorAggregate<int, ?array<string, mixed>>
 */
class DeputyshipCandidatesGroup implements \IteratorAggregate
{
    public string $orderUid;

    /** @var ?array<string, mixed> */
    public ?array $insertOrder = null;

    /** @var array<array<string, mixed>> */
    public array $insertOthers = [];

    /** @var array<array<string, mixed>> */
    public array $updates = [];

    /**
     * If group is invalid (i.e. has multiple order UIDs) this returns null, as a candidate group
     * cannot be created.
     *
     * @param array<array<string, mixed>> $candidatesList List of candidates represented as arrays
     */
    public static function create(string $orderUid, array $candidatesList): ?self
    {
        $group = new self();

        $group->orderUid = $orderUid;

        /** @var array<string, mixed> $candidate */
        foreach ($candidatesList as $candidate) {
            if ($candidate['orderUid'] !== $orderUid) {
                return null;
            }

            switch ($candidate['action']) {
                case DeputyshipCandidateAction::InsertOrder:
                    $group->insertOrder = $candidate;
                    break;
                case DeputyshipCandidateAction::InsertOrderNdr:
                case DeputyshipCandidateAction::InsertOrderReport:
                case DeputyshipCandidateAction::InsertOrderDeputy:
                    $group->insertOthers[] = $candidate;
                    break;
                case DeputyshipCandidateAction::UpdateDeputyStatus:
                case DeputyshipCandidateAction::UpdateOrderStatus:
                    $group->updates[] = $candidate;
                    break;
            }
        }

        return $group;
    }

    /**
     * @return \Traversable<?array<string, mixed>>
     */
    public function getIterator(): \Traversable
    {
        yield $this->insertOrder;

        foreach ($this->insertOthers as $insertOther) {
            yield $insertOther;
        }

        foreach ($this->updates as $update) {
            yield $update;
        }
    }

    public function totalCandidates(): int
    {
        return (is_null($this->insertOrder) ? 0 : 1) + count($this->updates) + count($this->insertOthers);
    }
}
