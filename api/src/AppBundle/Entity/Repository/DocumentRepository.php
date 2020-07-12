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
c.case_number as case_number
from (((document as d
inner join report as r on d.report_id = r.id)
inner join report_submission as rs on d.report_submission_id  = rs.id)
inner join client as c on r.client_id = c.id)
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
                'id' => $row['document_id'],
                'document_created_on' => $row['document_created_on'],
                'report_submission_id' => $row['report_submission_id'],
                'ndr_id' => $row['ndr_id'],
                'report_id' => $row['report_id'],
                'report_start_date' => $row['report_start_date'],
                'report_end_date' => $row['report_end_date'],
                'report_submit_date' => $row['report_submit_date'],
                'report_type' => $row['report_type'],
                'is_report_pdf' => $row['is_report_pdf'],
                'filename' => $row['filename'],
                'storage_reference' => $row['storage_reference'],
                'uuid' => $row['opg_uuid'],
                'case_number' => $row['case_number']
            ];

            $reportSubmissionIds[] = $row['report_submission_id'];
        }

        $idsString = implode(",", array_unique($reportSubmissionIds));

        $getReportSubmissionsQuery = "SELECT * FROM report_submission WHERE id IN ($idsString) ORDER BY created_on ASC;";

        $submissionStmt = $conn->prepare($getReportSubmissionsQuery);
        $submissionStmt->execute();
        $results = $submissionStmt->fetchAll(PDO::FETCH_ASSOC);

        $reportSubmissions = [];

        foreach ($results as $row) {
            $reportSubmissions[$row['id']] = [
                'id' => $row['id'],
                'opg_uuid' => $row['opg_uuid'],
                'created_on' => $row['created_on'],
                'report_id' => $row['report_id'],
                'contains_report_pdf' => false
            ];
        }

        $lastUuid = null;
        $lastReportId = null;

        foreach($reportSubmissions as $subIndex => $reportSubmission) {
            if (!is_null($reportSubmission['opg_uuid'])) {
                $lastUuid = $reportSubmission['opg_uuid'];
                $lastReportId = $reportSubmission['report_id'];
                continue;
            }

            if (is_null($reportSubmission['opg_uuid']) && $reportSubmission['report_id'] === $lastReportId) {
                $reportSubmission['opg_uuid'] = $lastUuid;
                $reportSubmissions[$subIndex] = $reportSubmission;
            }
        }



        // Set if a submission contains a report pdf
        // Assign submission uuid to $document where report_submission_id == report submission id and $document is a pdf
        // Assign submission uuid to $document where report_submission_id == report submission id and submission contains a pdf
        // Assign submission uuid to $document where submission does not contain a pdf but relates to another submission that shares a the same report id

        // Look at getting extra columns (uuid and client id) from query rather than looping through and assigning


        // Determine if a report submission contains a report pdf and assignn report uuids where possible
        foreach ($documents as $docIndex => $document) {
            foreach ($reportSubmissions as $subIndex => $reportSubmission) {
                if ($document['report_submission_id'] === $reportSubmission['id']) {
                    $documents[$docIndex]['uuid'] = $reportSubmission['opg_uuid'];

//                    $reportSubmission['documents'] = $document;
//                    if ($document['is_report_pdf']) {
//                        $reportSubmission['contains_report_pdf'] = true;
//                        $reportSubmissions[$subIndex] = $reportSubmission;
//                        break;
//                    }
                }
            }
        }

//        $lastUuid = null;
//
//        foreach ($documents as $docIndex => $document) {
//            $groupedSubmissions = [];
//
//            foreach($reportSubmissions as $subIndex => $reportSubmission) {
//                if (!is_null($reportSubmission['opg_uuid'])) {
////                    $groupedSubmissions[$reportSubmission['opg_uuid']][] = $reportSubmission;
//                    $lastUuid = $reportSubmission['opg_uuid'];
//                    break;
//                }
//
//                if (is_null($reportSubmission['opg_uuid'])) {
//                    $reportSubmission['opg_uuid'] = $lastUuid;
//                    $reportSubmissions[$subIndex] = $reportSubmission;
////                    $groupedSubmissions[$lastUuid][] = $reportSubmission;
//                }
//            }
//
//            $documents[$docIndex]['submissions_grouped_by_report_pdfs'] = $groupedSubmissions;
//        }

        // Sort newest to oldest as we will always be interested in the most recent report pdf submission for uuids
//        usort($reportSubmissions, function($a1, $a2) {
//            $v1 = strtotime($a1['created_on']);
//            $v2 = strtotime($a2['created_on']);
//            return $v2 - $v1;
//        });
//
        // Assign UUIDs to supporting documents where possible
//        foreach ($documents as $index => $document) {
//            $submissionId = $document['report_submission_id'];
//
//            if (!$document['is_report_pdf'] && array_key_exists($submissionId, $reportSubmissions) && !$reportSubmissions[$submissionId]['contains_report_pdf'] && is_null($document['uuid'])) {
//
//                foreach($reportSubmissions as $subIndex => $sub) {
//                    if ($sub['report_id'] === $document['report_id'] && $sub['contains_report_pdf'] && $sub['created_on'] < $document['document_created_on'] ) {
//                        $document['uuid'] = $reportSubmissions[$subIndex]['opg_uuid'];
//                        $documents[$index] = $document;
//                        break;
//                    }
//                }
//            }
//        }

        return $documents;


        // Using DENSE_RANK here as we get multiple rows for the same document due to multiple report submissions. This
        // ensures any limit applied will not miss out submissions by chance. Also to note, the report_submission_uuid
        // returned will be related to the report submission that contains a report PDF that has been synced. This
        // means additional docs submissions will use the existing submission UUID but re-submissions will have their
        // own UUID (apologies to anyone in advance that needs to amend this statement).
//        $queuedDocumentsQuery = "
//SELECT case_number,
//document_id,
//document_report_submission_id,
//is_report_pdf,
//filename,
//report_submission_uuid,
//storage_reference,
//report_start_date,
//report_end_date,
//report_submit_date,
//report_type,
//ndr_id,
//ndr_start_date,
//ndr_submit_date
//FROM (
//SELECT
//ROW_NUMBER() OVER (PARTITION BY document_id order by all_report_submission_id desc) as rown,
//case_number,
//document_id,
//document_report_submission_id,
//is_report_pdf,
//filename,
//storage_reference,
//report_start_date,
//report_end_date,
//report_submit_date,
//report_type,
//ndr_id,
//ndr_start_date,
//ndr_submit_date,
//coalesce(report_submission_uuid, all_uuid) as report_submission_uuid
//FROM (
//SELECT DENSE_RANK() OVER(ORDER BY d.is_report_pdf DESC, d.id) AS dn,
//coalesce(c1.case_number, c2.case_number) AS case_number,
//rs2.opg_uuid as all_uuid,
//rs.opg_uuid AS report_submission_uuid,
//d.id AS document_id,
//d.is_report_pdf,
//d.filename,
//d.storage_reference,
//d.report_submission_id AS document_report_submission_id,
//rs2.id as all_report_submission_id,
//r.start_date AS report_start_date,
//r.end_date AS report_end_date,
//r.submit_date AS report_submit_date,
//r.type AS report_type,
//o.id AS ndr_id,
//o.start_date AS ndr_start_date,
//o.submit_date AS ndr_submit_date
//FROM document d
//LEFT JOIN report_submission rs ON rs.id = d.report_submission_id
//LEFT JOIN report_submission rs2 on rs2.report_id = d.report_id and d.created_on > rs2.created_on
//LEFT JOIN report r ON r.id = d.report_id
//LEFT JOIN odr o ON o.id = d.ndr_id
//LEFT JOIN client c1 ON c1.id = r.client_id
//LEFT JOIN client c2 ON c2.id = o.client_id
//WHERE d.synchronisation_status = 'QUEUED'
//) AS sub WHERE dn < $limit) as sub2
//WHERE sub2.rown = 1;";
//
//        $conn = $this->getEntityManager()->getConnection();
//        $stmt = $conn->prepare($queuedDocumentsQuery);
//        $stmt->execute();
//
//        $queuedDocumentData = [];
//
//        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
//        {
//            if (!isset($queuedDocumentData[$row['document_id']])) {
//                $queuedDocumentData[$row['document_id']] = [
//                    'document_id' => $row['document_id'],
//                    'report_submission_id' => $row['document_report_submission_id'],
//                    'ndr_id' => $row['ndr_id'],
//                    'case_number' => $row['case_number'],
//                    'is_report_pdf' => $row['is_report_pdf'],
//                    'filename' => $row['filename'],
//                    'storage_reference' => $row['storage_reference'],
//                    'report_start_date' => isset($row['report_start_date']) ? $row['report_start_date'] : (new DateTime($row['ndr_start_date']))->format('Y-m-d'),
//                    'report_end_date' => $row['report_end_date'],
//                    'report_submit_date' => isset($row['report_submit_date']) ? $row['report_submit_date'] : $row['ndr_submit_date'],
//                    'report_type' => $row['report_type'],
//                    'report_submission_uuid' => $row['report_submission_uuid']
//                ];
//            }
//        }
//
//        if (count($queuedDocumentData)) {
//            // Set documents to in progress to ensure additional runs won't pick up the same documents
//            $ids = [];
//            foreach($queuedDocumentData as $queuedDocumentDatum) {
//                $ids[] = $queuedDocumentDatum['document_id'];
//            }
//
//            $idsString = implode(",", $ids);
//
//            $updateStatusQuery = "UPDATE document SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
//            $stmt = $conn->prepare($updateStatusQuery);
//
//            $stmt->execute();
//        }
//
//        return $queuedDocumentData;
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
