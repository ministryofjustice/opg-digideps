<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function getQueuedDocumentsAndSetToInProgress(string $limit)
    {
        $queuedDocumentsQuery = "
        SELECT d.id as document_id,
        d.created_on as document_created_on,
        d.report_submission_id as report_submission_id,
        d.is_report_pdf as is_report_pdf,
        d.filename as filename,
        d.storage_reference as storage_reference,
        d.report_id as report_id,
        d.ndr_id as ndr_id,
        d.sync_attempts as document_sync_attempts,
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
        ORDER BY is_report_pdf DESC, report_submission_id ASC
        LIMIT $limit;";

        $conn = $this->getEntityManager()->getConnection();

        $docStmt = $conn->prepare($queuedDocumentsQuery);
        $result = $docStmt->executeQuery();

        $documents = [];
        $reportIds = [];
        $ndrIds = [];

        // Get all queued documents
        $results = $result->fetchAllAssociative();
        foreach ($results as $row) {
            $documents[$row['document_id']] = [
                'document_id' => $row['document_id'],
                'document_created_on' => $row['document_created_on'],
                'report_submission_id' => $row['report_submission_id'],
                'ndr_id' => $row['ndr_id'],
                'report_id' => $row['report_id'],
                'report_start_date' => isset($row['report_start_date']) ? $row['report_start_date'] : (new \DateTime($row['ndr_start_date']))->format('Y-m-d'),
                'report_end_date' => $row['report_end_date'],
                'report_submit_date' => isset($row['report_submit_date']) ? $row['report_submit_date'] : $row['ndr_submit_date'],
                'report_type' => $row['report_type'],
                'is_report_pdf' => $row['is_report_pdf'],
                'filename' => $row['filename'],
                'storage_reference' => $row['storage_reference'],
                'report_submission_uuid' => $row['opg_uuid'],
                'case_number' => $row['case_number'],
                'document_sync_attempts' => $row['document_sync_attempts'],
            ];

            if (!empty($row['report_id'])) {
                $reportIds[] = $row['report_id'];
            }

            if (!empty($row['ndr_id'])) {
                $ndrIds[] = $row['ndr_id'];
            }
        }

        if (count($documents) > 0) {
            $getReportSubmissionsQuery = $this->buildReportSubmissionsQuery(
                array_values(array_filter(array_unique($reportIds))),
                array_values(array_filter(array_unique($ndrIds)))
            );

            $submissionStmt = $conn->prepare($getReportSubmissionsQuery);
            $result = $submissionStmt->executeQuery();
            $submissions = $result->fetchAllAssociative();

            $reportPdfFlaggedSubmissions = $this->flagSubmissionsContainingReportPdfs($submissions, $conn);
            $groupedSubmissions = $this->groupSubmissionsByReportId($reportPdfFlaggedSubmissions);
            $groupedSubmissionsWithUuids = $this->assignUuidsToAdditionalDocumentSubmissions($groupedSubmissions);
            $documentsWithUuids = $this->extractUuidsFromSubmissionsAndAssignToDocuments($documents, $groupedSubmissionsWithUuids);

            $this->setQueuedDocumentsToInProgress($documentsWithUuids, $conn);

            return $documentsWithUuids;
        }

        return [];
    }

    public function getResubmittableErrorDocumentsAndSetToQueued(string $limit)
    {
        $resubmittableErrorDocumentsQuery = "
SELECT d.id AS document_id,
d.created_on AS document_created_on,
d.report_submission_id AS report_submission_id,
d.is_report_pdf AS is_report_pdf,
d.filename AS filename,
d.storage_reference AS storage_reference,
d.report_id AS report_id,
d.ndr_id AS ndr_id,
d.sync_attempts AS document_sync_attempts,
r.start_date AS report_start_date,
r.end_date AS report_end_date,
r.submit_date AS report_submit_date,
r.type AS report_type,
rs.opg_uuid AS opg_uuid,
rs.created_on AS report_submission_created_on,
o.start_date AS ndr_start_date,
o.submit_date AS ndr_submit_date,
COALESCE(c1.case_number, c2.case_number) AS case_number
FROM document AS d
LEFT JOIN report AS r on d.report_id = r.id
LEFT JOIN odr AS o on d.ndr_id = o.id
LEFT JOIN report_submission AS rs on d.report_submission_id  = rs.id
LEFT JOIN client AS c1 on r.client_id = c1.id
LEFT JOIN client AS c2 on o.client_id = c2.id
WHERE
    (
        d.synchronisation_status='PERMANENT_ERROR'
        AND
            (
                d.synchronisation_error LIKE 'Report PDF failed to sync%'
                OR
                d.synchronisation_error LIKE 'Document failed to sync after%'
                OR
                d.synchronisation_error LIKE '%OPGDATA-API-FORBIDDEN%'
            )
    ) OR
    (
        d.synchronisation_status='IN_PROGRESS'
        AND
        rs.created_on < (CURRENT_DATE - 1)
    )
ORDER BY is_report_pdf DESC, report_submission_id ASC
LIMIT $limit;";

        $conn = $this->getEntityManager()->getConnection();

        $docStmt = $conn->prepare($resubmittableErrorDocumentsQuery);
        $result = $docStmt->executeQuery();

        $documents = [];

        // Get all queued documents
        $results = $result->fetchAllAssociative();
        foreach ($results as $row) {
            $documents[$row['document_id']] = [
                'document_id' => $row['document_id'],
                'document_created_on' => $row['document_created_on'],
                'report_submission_id' => $row['report_submission_id'],
                'ndr_id' => $row['ndr_id'],
                'report_id' => $row['report_id'],
                'report_start_date' => isset($row['report_start_date']) ? $row['report_start_date'] : (new \DateTime($row['ndr_start_date']))->format('Y-m-d'),
                'report_end_date' => $row['report_end_date'],
                'report_submit_date' => isset($row['report_submit_date']) ? $row['report_submit_date'] : $row['ndr_submit_date'],
                'report_type' => $row['report_type'],
                'is_report_pdf' => $row['is_report_pdf'],
                'filename' => $row['filename'],
                'storage_reference' => $row['storage_reference'],
                'report_submission_uuid' => $row['opg_uuid'],
                'case_number' => $row['case_number'],
                'document_sync_attempts' => $row['document_sync_attempts'],
            ];
        }

        if (count($documents) > 0) {
            $this->setErrorDocumentsToQueued($documents, $conn);

            return $documents;
        }

        return [];
    }

    public function logFailedDocuments()
    {
        $queuedStatus = Document::SYNC_STATUS_QUEUED;
        $inProgressStatus = Document::SYNC_STATUS_IN_PROGRESS;
        $temporaryErrorStatus = Document::SYNC_STATUS_TEMPORARY_ERROR;
        $permanentErrorStatus = Document::SYNC_STATUS_PERMANENT_ERROR;

        $queuedDocumentsQuery = "
SELECT
  COALESCE(SUM(CASE WHEN d.synchronisation_status = '{$queuedStatus}' AND rs.created_on < NOW() - INTERVAL '1 HOUR' THEN 1 ELSE 0 END), 0) AS queued_over_1_hour,
  COALESCE(SUM(CASE WHEN d.synchronisation_status = '{$inProgressStatus}' AND rs.created_on < NOW() - INTERVAL '1 HOUR' THEN 1 ELSE 0 END), 0) AS in_progress_over_1_hour,
  COALESCE(SUM(CASE WHEN d.synchronisation_status = '{$temporaryErrorStatus}' THEN 1 ELSE 0 END), 0) AS temporary_error_count,
  COALESCE(SUM(CASE WHEN d.synchronisation_status = '{$permanentErrorStatus}' THEN 1 ELSE 0 END), 0) AS permanent_error_count
FROM document d
INNER JOIN report_submission rs ON d.report_submission_id = rs.id
WHERE rs.archived is false
AND d.synchronisation_status IN ('{$queuedStatus}', '{$permanentErrorStatus}', '{$temporaryErrorStatus}', '{$inProgressStatus}')
";
        $conn = $this->getEntityManager()->getConnection();

        $failedDocumentStmt = $conn->prepare($queuedDocumentsQuery);
        $result = $failedDocumentStmt->executeQuery();

        $results = $result->fetchAllAssociative();

        $failedCounts = [];
        $i = 0;
        foreach ($results as $row) {
            $failedCounts[$i] = [
                'queued_over_1_hour' => $row['queued_over_1_hour'],
                'in_progress_over_1_hour' => $row['in_progress_over_1_hour'],
                'temporary_error_count' => $row['temporary_error_count'],
                'permanent_error_count' => $row['permanent_error_count'],
            ];
            ++$i;
        }
        if (1 != count($failedCounts)) {
            return [];
        }

        return $failedCounts[0];
    }

    public function updateSupportingDocumentStatusByReportSubmissionIds(array $reportSubmissionIds, ?string $syncErrorMessage = null)
    {
        $idsString = implode(',', $reportSubmissionIds);
        $status = Document::SYNC_STATUS_PERMANENT_ERROR;

        $updateStatusQuery = "
UPDATE document
SET synchronisation_status = '$status', synchronisation_error = '$syncErrorMessage'
WHERE report_submission_id IN ($idsString)
AND is_report_pdf=false";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($updateStatusQuery);

        return $stmt->executeStatement();
    }

    private function flagSubmissionsContainingReportPdfs(array $reportSubmissions, Connection $connection)
    {
        $submissionIds = array_map(function ($submission) {
            return $submission['id'];
        }, $reportSubmissions);

        $submissionIdStrings = implode(',', $submissionIds);

        $stmt = $connection->prepare("SELECT * FROM document WHERE report_submission_id IN ($submissionIdStrings) ORDER BY created_on ASC");
        $result = $stmt->executeQuery();
        $documents = $result->fetchAllAssociative();

        foreach ($reportSubmissions as $i => $submission) {
            foreach ($documents as $document) {
                if ($document['report_submission_id'] === $submission['id'] && $document['is_report_pdf']) {
                    $reportSubmissions[$i]['contains_report_pdf'] = true;
                    break;
                } else {
                    $reportSubmissions[$i]['contains_report_pdf'] = false;
                }
            }
        }

        return $reportSubmissions;
    }

    private function groupSubmissionsByReportId(array $reportSubmissions)
    {
        $groupedReportSubmissions = ['reports' => [], 'ndrs' => []];

        foreach ($reportSubmissions as $row) {
            if (!is_null($row['report_id'])) {
                $groupedReportSubmissions['reports'][$row['report_id']][] = [
                    'id' => $row['id'],
                    'opg_uuid' => $row['opg_uuid'],
                    'created_on' => $row['created_on'],
                    'report_id' => $row['report_id'],
                    'ndr_id' => $row['ndr_id'],
                    'contains_report_pdf' => $row['contains_report_pdf'],
                ];
            }

            if (!is_null($row['ndr_id'])) {
                $groupedReportSubmissions['ndrs'][$row['ndr_id']][] = [
                    'id' => $row['id'],
                    'opg_uuid' => $row['opg_uuid'],
                    'created_on' => $row['created_on'],
                    'report_id' => $row['report_id'],
                    'ndr_id' => $row['ndr_id'],
                    'contains_report_pdf' => $row['contains_report_pdf'],
                ];
            }
        }

        // Flag resubmissions to handle UUIDs correctly
        foreach ($groupedReportSubmissions['reports'] as $reportId => $submissions) {
            $firstSubmissionDate = $submissions[0]['created_on'];

            foreach ($submissions as $i => $submission) {
                if ($submission['contains_report_pdf'] && $submission['created_on'] > $firstSubmissionDate) {
                    $groupedReportSubmissions['reports'][$reportId][$i]['is_resubmission'] = true;
                } else {
                    $groupedReportSubmissions['reports'][$reportId][$i]['is_resubmission'] = false;
                }
            }
        }

        return $groupedReportSubmissions;
    }

    private function assignUuidsToAdditionalDocumentSubmissions(array $reportSubmissions): array
    {
        $lastUuid = null;
        $lastReportId = null;

        // Walk through the submissions grouped by report id to assign missing uuids to additional submissions
        foreach ($reportSubmissions['reports'] as $reportId => $groupedSubmissions) {
            foreach ($groupedSubmissions as $key => $reportSubmission) {
                // We only want to pass on UUIDs associated with a submission containing a report PDF to create correct folders in Sirius
                if (!is_null($reportSubmission['opg_uuid']) && true === $reportSubmission['contains_report_pdf']) {
                    $lastUuid = $reportSubmission['opg_uuid'];
                    $lastReportId = $reportSubmission['report_id'];
                    continue;
                }

                if (is_null($reportSubmission['opg_uuid']) && $reportSubmission['report_id'] === $lastReportId && !$reportSubmission['is_resubmission']) {
                    $reportSubmissions['reports'][$reportId][$key]['opg_uuid'] = $lastUuid;
                }
            }
        }

        return $reportSubmissions;
    }

    private function extractUuidsFromSubmissionsAndAssignToDocuments(array $documents, array $reportSubmissions): array
    {
        // Extract the uuids from the submissions and assign to the queued documents data array
        foreach ($documents as $docIndex => $document) {
            if (is_null($document['report_submission_uuid'])) {
                foreach ($reportSubmissions['reports'] as $reportId => $groupedSubmissions) {
                    foreach ($groupedSubmissions as $submission) {
                        if ($document['report_submission_id'] === $submission['id']) {
                            $documents[$docIndex]['report_submission_uuid'] = $submission['opg_uuid'];
                            break;
                        }
                    }
                }
            }
        }

        return $documents;
    }

    private function buildReportSubmissionsQuery(array $reportIds, array $ndrIds)
    {
        $reportIdsString = implode(',', $reportIds);
        $ndrIdsString = implode(',', $ndrIds);

        if (count($reportIds) > 0 && count($ndrIds) < 1) {
            return "SELECT * FROM report_submission WHERE (report_id IN ($reportIdsString)) ORDER BY created_on ASC;";
        }

        if (count($ndrIds) > 0 && count($reportIds) < 1) {
            return "SELECT * FROM report_submission WHERE (ndr_id IN ($ndrIdsString)) ORDER BY created_on ASC;";
        }

        if (count($reportIds) > 0 && count($ndrIds) > 0) {
            return "SELECT * FROM report_submission WHERE (report_id IN ($reportIdsString)) OR (ndr_id IN ($ndrIdsString)) ORDER BY created_on ASC;";
        }
    }

    /**
     * @throws Exception
     */
    private function setQueuedDocumentsToInProgress(array $documents, Connection $connection): void
    {
        if (count($documents)) {
            // Set documents to in progress to ensure additional runs won't pick up the same documents
            $ids = [];
            foreach ($documents as $data) {
                $ids[] = $data['document_id'];
            }
            $idsString = implode(',', $ids);

            $updateStatusQuery = "UPDATE document SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
            $stmt = $connection->prepare($updateStatusQuery);

            $stmt->executeQuery();
        }
    }

    /**
     * @throws Exception
     */
    private function setErrorDocumentsToQueued(array $documents, Connection $connection): void
    {
        if (count($documents)) {
            // Set documents to queued where they are re-submittable
            $ids = [];
            foreach ($documents as $data) {
                $ids[] = $data['document_id'];
            }

            $idsString = implode(',', $ids);

            $updateStatusQuery = "
UPDATE document
SET synchronisation_status = 'QUEUED', synchronisation_error = ''
WHERE id IN ($idsString)";
            $stmt = $connection->prepare($updateStatusQuery);

            $stmt->executeQuery();
        }
    }
}
