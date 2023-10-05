<?php

declare(strict_types=1);

namespace App\Model\Sirius;

class SiriusSupportingDocumentMetadata implements SiriusMetadataInterface
{
    /** @var int */
    private $submissionId;

    public function getSubmissionId(): int
    {
        return $this->submissionId;
    }

    /**
     * @return SiriusSupportingDocumentMetadata
     */
    public function setSubmissionId(int $submissionId): self
    {
        $this->submissionId = $submissionId;

        return $this;
    }
}
