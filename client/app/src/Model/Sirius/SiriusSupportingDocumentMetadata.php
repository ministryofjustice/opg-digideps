<?php

declare(strict_types=1);

namespace App\Model\Sirius;

/**
 * This class is only serialized, which is why it has unused getters.
 */
class SiriusSupportingDocumentMetadata implements SiriusMetadataInterface
{
    private int $submissionId;

    public function getSubmissionId(): int
    {
        return $this->submissionId;
    }

    public function setSubmissionId(int $submissionId): self
    {
        $this->submissionId = $submissionId;

        return $this;
    }
}
