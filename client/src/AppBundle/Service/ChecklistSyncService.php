<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Model\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusSupportingDocumentMetadata;
use function GuzzleHttp\Psr7\mimetype_from_filename;

class ChecklistSyncService
{
    /**
     * @param Checklist $checklist
     */
    public function sync(Checklist $checklist)
    {

    }

    private function buildUpload(Checklist $checklist, string $content)
    {
        // Retrieve an array from api:
        /**
         * [
         *   'checklist' => ['all checklist columns'],
         *   'review-checklist' => ['all review-checklist columns]
         * ]
         *
         * review-checklist will very often be null
         * Then send that array to the ->render on the HTML twig template to create the content
         * Send the string content to wkhtmltopdf to generate the file content
         */


//        $file = (new SiriusDocumentFile())
//            ->setName($documentData->getFileName())
//            ->setMimetype(mimetype_from_filename($documentData->getFileName()))
//            ->setSource(base64_encode($content));

//        return (new SiriusDocumentUpload())
//            ->setType('checklists')
//            ->setAttributes(null)
//            ->setFile($file);
    }

    /**
     * @return array
     */
    public function getSyncErrorSubmissionIds(): array
    {
        return [];
    }

    /**
     * @param array $ids
     */
    public function setSyncErrorSubmissionIds(array $ids): void
    {

    }

    /**
     * @return int
     */
    public function getChecklistsNotSyncedCount(): int
    {
        return 0;
    }

    /**
     * @param int $count
     */
    public function setChecklistsNotSyncedCount(int $count): void
    {

    }

    public function setChecklistsToPermanentError(): void
    {

    }

}
