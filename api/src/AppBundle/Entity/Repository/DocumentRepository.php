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
        $queuedDocumentsQuery = "
SELECT d.id as document_id,
d.report_submission_id as report_submission_id,
d.is_report_pdf as is_report_pdf,
d.filename as filename,
d.storage_reference as storage_reference,
d.report_id as report_id,
d.ndr_id as ndr_id,
d.created_on as document_created_on,
r.start_date as report_start_date,
r.end_date as report_end_date,
r.submit_date as report_submit_date,
r.type as report_type,
rs.opg_uuid as opg_uuid,
rs.created_on as report_submission_created_on,
o.start_date as ndr_start_date,
o.submit_date as ndr_submit_date,
coalesce(c1.case_number, c2.case_number) AS case_number
FROM document as d
LEFT JOIN report as r on d.report_id = r.id
LEFT JOIN odr as o on d.ndr_id = o.id
LEFT JOIN report_submission as rs on d.report_submission_id  = rs.id
LEFT JOIN client as c1 on r.client_id = c1.id
LEFT JOIN client as c2 on o.client_id = c2.id
WHERE synchronisation_status='QUEUED'
LIMIT $limit;";

        $conn = $this->getEntityManager()->getConnection();

        $docStmt = $conn->prepare($queuedDocumentsQuery);
        $docStmt->execute();

        $documents = [];
        $reportSubmissionIds = [];

        // Get all queued documents
        $results = $docStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $documents[$row['document_id']] = [
                'document_id' => $row['document_id'],
                'document_created_on' => $row['document_created_on'],
                'report_submission_id' => $row['report_submission_id'],
                'ndr_id' => $row['ndr_id'],
                'report_id' => $row['report_id'],
                'report_start_date' => isset($row['report_start_date']) ? $row['report_start_date'] : (new DateTime($row['ndr_start_date']))->format('Y-m-d'),
                'report_end_date' => $row['report_end_date'],
                'report_submit_date' => isset($row['report_submit_date']) ? $row['report_submit_date'] : $row['ndr_submit_date'],
                'report_type' => $row['report_type'],
                'is_report_pdf' => $row['is_report_pdf'],
                'filename' => $row['filename'],
                'storage_reference' => $row['storage_reference'],
                'report_submission_uuid' => $row['opg_uuid'],
                'case_number' => $row['case_number']
            ];


            $reportSubmissionIds[] = $row['report_submission_id'];
        }

        $idsString = implode(",", array_unique($reportSubmissionIds));

        $getReportSubmissionsQuery = "SELECT * FROM report_submission WHERE id IN ($idsString) ORDER BY created_on ASC;";

        $submissionStmt = $conn->prepare($getReportSubmissionsQuery);
        $submissionStmt->execute();
        $results = $submissionStmt->fetchAll(PDO::FETCH_ASSOC);

        $reportSubmissions = ['reports' => [], 'ndrs' => []];

        foreach ($results as $row) {
            if (!is_null($row['report_id'])) {
                $reportSubmissions['reports'][$row['report_id']][] = [
                    'id' => $row['id'],
                    'opg_uuid' => $row['opg_uuid'],
                    'created_on' => $row['created_on'],
                    'report_id' => $row['report_id'],
                    'ndr_id' => $row['ndr_id'],
                ];
            }

            if (!is_null($row['ndr_id'])) {
                $reportSubmissions['ndrs'][$row['ndr_id']][] = [
                    'id' => $row['id'],
                    'opg_uuid' => $row['opg_uuid'],
                    'created_on' => $row['created_on'],
                    'report_id' => $row['report_id'],
                    'ndr_id' => $row['ndr_id'],
                ];
            }
        }


        $lastUuid = null;
        $lastReportId = null;

        // Walk through the submissions grouped by report id to assign missing uuids to additional submissions
        foreach ($reportSubmissions['reports'] as $reportId => $groupedSubmissions) {
            foreach ($groupedSubmissions as $key => $reportSubmission) {
                if (!is_null($reportSubmission['opg_uuid'])) {
                    $lastUuid = $reportSubmission['opg_uuid'];
                    $lastReportId = $reportSubmission['report_id'];
                    continue;
                }

                if (is_null($reportSubmission['opg_uuid']) && $reportSubmission['report_id'] === $lastReportId) {
                    $reportSubmissions['reports'][$reportId][$key]['opg_uuid'] = $lastUuid;
                }
            }
        }

        // Extract the uuids from the submissions and assign to the queued documents data array
        foreach ($documents as $docIndex => $document) {
            if (is_null($document['report_submission_uuid'])) {
                foreach ($reportSubmissions['reports'] as $reportId => $groupedSubmissions) {
                    foreach ($groupedSubmissions as $submission) {
                        if ($document['report_submission_id'] === $submission['id'] ) {
                            $documents[$docIndex]['report_submission_uuid'] = $submission['opg_uuid'];
                            break;
                        }
                    }
                }
            }
        }

        if (count($documents)) {
            // Set documents to in progress to ensure additional runs won't pick up the same documents
            $ids = [];
            foreach ($documents as $data) {
                $ids[] = $data['document_id'];

                $idsString = implode(",", $ids);

                $updateStatusQuery = "UPDATE document SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
                $stmt = $conn->prepare($updateStatusQuery);

                $stmt->execute();
            }
        }

        return $documents;
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
