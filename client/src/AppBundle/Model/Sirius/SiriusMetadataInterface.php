<?php declare(strict_types=1);


namespace AppBundle\Model\Sirius;


interface SiriusMetadataInterface
{
    public function getSubmissionId();
    public function setSubmissionId(int $submissionId);
}
