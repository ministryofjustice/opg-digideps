<?php declare(strict_types=1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Document;
use DateTime;
use PDO;

class DocumentRepository extends AbstractEntityRepository
{
    /**
     * Get soft-deleted documents
     *
     * @return Document[]
     */
    public function retrieveSoftDeleted()
    {
        $qb = $this->createQueryBuilder('d')
                ->where('d.deletedAt IS NOT NULL');

        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Document::class);
        $records = $qb->getQuery()->getResult(); /* @var $records Document[] */
        $this->_em->getFilters()->enable('softdeleteable');

        return $records;
    }


    public function getQueuedDocumentsAndSetToInProgress(string $limit)
    {
        // Using DENSE_RANK here as we get multiple rows for the same document due to multiple report submissions. This
        // ensures any limit applied will not miss out submissions by chance
        $queuedDocumentsQuery = "
SELECT case_number,
document_id,
document_report_submission_id,
is_report_pdf,
filename,
storage_reference,
report_start_date,
report_end_date,
report_submit_date,
report_type,
ndr_id,
ndr_start_date,
ndr_submit_date,
report_submission_id,
report_submission_uuid
FROM (
SELECT DENSE_RANK() OVER(ORDER BY d.is_report_pdf DESC, d.id) AS dn,
coalesce(c1.case_number, c2.case_number) AS case_number,
coalesce(rs1.id, rs2.id) AS report_submission_id,
coalesce(rs1.opg_uuid, rs2.opg_uuid) AS report_submission_uuid,
d.id AS document_id, d.is_report_pdf, d.filename, d.storage_reference, d.report_submission_id AS document_report_submission_id,
r.start_date AS report_start_date, r.end_date AS report_end_date, r.submit_date AS report_submit_date, r.type AS report_type,
o.id AS ndr_id, o.start_date AS ndr_start_date, o.submit_date AS ndr_submit_date
FROM document d
LEFT JOIN report r ON r.id = d.report_id
LEFT JOIN odr o ON o.id = d.ndr_id
LEFT JOIN report_submission rs1 ON rs1.id = d.report_submission_id
LEFT JOIN report_submission rs2 ON rs2.id = d.report_submission_id
LEFT JOIN client c1 ON c1.id = r.client_id
LEFT JOIN client c2 ON c2.id = o.client_id
WHERE d.synchronisation_status = 'QUEUED') AS sub
WHERE dn < $limit;";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($queuedDocumentsQuery);
        $stmt->execute();

        $queuedDocumentDatas = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            if (!isset($queuedDocumentDatas[$row['document_id']])) {
                $queuedDocumentDatas[$row['document_id']] = [
                    'document_id' => $row['document_id'],
                    'report_submission_id' => $row['document_report_submission_id'],
                    'ndr_id' => $row['ndr_id'],
                    'case_number' => $row['case_number'],
                    'is_report_pdf' => $row['is_report_pdf'],
                    'filename' => $row['filename'],
                    'storage_reference' => $row['storage_reference'],
                    'report_start_date' => isset($row['report_start_date']) ? $row['report_start_date'] : (new DateTime($row['ndr_start_date']))->format('Y-m-d'),
                    'report_end_date' => $row['report_end_date'],
                    'report_submit_date' => isset($row['report_submit_date']) ? $row['report_submit_date'] : $row['ndr_submit_date'],
                    'report_type' => $row['report_type'],
                    'report_submission_uuid' => $row['report_submission_uuid']
                ];
            }
        }

        if (count($queuedDocumentDatas)) {
            // Set documents to in progress to ensure additional runs won't pick up the same documents
            $ids = [];
            foreach($queuedDocumentDatas as $data) {
                $ids[] = $data['document_id'];
            }

            $idsString = implode(",", $ids);

            $updateStatusQuery = "UPDATE document SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
            $stmt = $conn->prepare($updateStatusQuery);

            $stmt->execute();
        }

        return $queuedDocumentDatas;
    }


    public function updateSupportingDocumentStatusByReportSubmissionIds(array $reportSubmissionIds, ?string $syncErrorMessage=null)
    {
        $idsString = implode(",", $reportSubmissionIds);
        $status = Document::SYNC_STATUS_PERMANENT_ERROR;

        $updateStatusQuery = "
UPDATE document
SET synchronisation_status = '$status', synchronisation_error = '$syncErrorMessage'
WHERE report_submission_id IN ($idsString)
AND is_report_pdf=false";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($updateStatusQuery);
        return $stmt->execute();
    }

}
