<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Entity\Report\Document;
use App\Exception\UnauthorisedException;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends RestController
{
    public const DOCUMENT_SYNC_ERROR_STATUSES = [Document::SYNC_STATUS_TEMPORARY_ERROR, Document::SYNC_STATUS_PERMANENT_ERROR];
    public const RETRIES_FAILED_MESSAGE = 'Document failed to sync after 4 attempts';
    public const REPORT_PDF_FAILED_MESSAGE = 'Report PDF failed to sync';

    private EntityManagerInterface $em;
    private AuthService $authService;
    private RestFormatter $formatter;
    private LoggerInterface $verboseLogger;
    private array $sectionIds = [EntityDir\Report\Report::SECTION_DOCUMENTS];

    public function __construct(EntityManagerInterface $em, AuthService $authService, RestFormatter $formatter, LoggerInterface $verboseLogger)
    {
        $this->authService = $authService;
        $this->em = $em;
        $this->formatter = $formatter;
        $this->verboseLogger = $verboseLogger;
    }

    /**
     * @Route("/document/{reportType}/{reportId}", requirements={
     *     "reportId":"\d+",
     *     "reportType" = "(report|ndr)"
     * }, methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportType, $reportId)
    {
        if ('report' === $reportType) {
            /** @var EntityDir\Report\Report $report */
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        } else {
            /** @var EntityDir\Report\Report $report */
            $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $reportId);
        }

        $this->denyAccessIfReportDoesNotBelongToUser($report);

        // hydrate and persist
        $data = $this->formatter->deserializeBodyContent($request, [
            'file_name' => 'notEmpty',
            'storage_reference' => 'notEmpty',
        ]);
        $document = new EntityDir\Report\Document($report);
        $document->setCreatedBy($this->getUser());
        $document->setFileName($data['file_name']);
        $document->setStorageReference($data['storage_reference']);
        $document->setIsReportPdf($data['is_report_pdf']);
        if (!$document->isAdminDocument()) {
            // only set flag to yes if document being added is a deputy Document (and not auto-generated)
            $report->setWishToProvideDocumentation('yes');
        }

        $this->em->persist($document);
        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $document->getId()];
    }

    /**
     * GET document by id.
     *
     * @Route("/document/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['documents'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        /* @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);

        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        return $document;
    }

    /**
     * Delete document.
     * Accessible only from deputy area.
     *
     * @Route("/document/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        /** @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);
        $report = $document->getReport();

        // enable if the check above is removed and the note is available for editing for the whole team
        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        $this->em->remove($document);
        $this->em->flush();

        // update yesno question to null if its the last document to be removed
        if (0 == count($report->getDeputyDocuments())) {
            $report->setWishToProvideDocumentation(null);
        }

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $id];
    }

    /**
     * Get queued documents.
     *
     * @Route("/document/queued", methods={"GET"})
     */
    public function getQueuedDocuments(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = $this->formatter->deserializeBodyContent($request);

        $documentRepo = $em->getRepository(Document::class);

        $failedDocuments = $documentRepo->logFailedDocuments();

        if (0 == count($failedDocuments)) {
            $this->verboseLogger->error('Unsupported number of rows from document sync counts');
        } else {
            $this->verboseLogger->notice(
                'queued_over_1_hour '.$failedDocuments['queued_over_1_hour'].
                ' in_progress_over_1_hour '.$failedDocuments['in_progress_over_1_hour'].
                ' temporary_error_count '.$failedDocuments['temporary_error_count'].
                ' permanent_error_count '.$failedDocuments['permanent_error_count']
            );
        }

        return json_encode($documentRepo->getQueuedDocumentsAndSetToInProgress($data['row_limit']));
    }

    // Duplicating above function until DDPB-4469 is played
    /**
     * Get queued documents.
     *
     * @Route("/document/queued-jwt", methods={"GET"})
     */
    public function getQueuedDocumentsJwt(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->authService->JWTIsValid($request)) {
            throw new UnauthorisedException('JWT is not valid');
        }
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = $this->formatter->deserializeBodyContent($request);

        $documentRepo = $em->getRepository(Document::class);

        return json_encode($documentRepo->getQueuedDocumentsAndSetToInProgress($data['row_limit']));
    }

    /**
     * Get queued documents.
     *
     * @Route("/document/update-related-statuses", methods={"PUT"})
     */
    public function updateRelatedDocumentStatuses(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $documentRepo = $em->getRepository(Document::class);

        $data = json_decode($request->getContent(), true);
        $reportSubmissionIds = $data['submissionIds'];
        $errorMessage = $data['errorMessage'];

        return json_encode($documentRepo->updateSupportingDocumentStatusByReportSubmissionIds($reportSubmissionIds, $errorMessage));
    }

    /**
     * Update a Document.
     *
     * @Route("/document/{id}", methods={"PUT"})
     */
    public function update(Request $request, int $id, EntityManagerInterface $em): Document
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = $this->formatter->deserializeBodyContent($request);

        /** @var Document $document */
        $documentRepository = $em->getRepository(Document::class);
        $document = $documentRepository->find($id);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['synchronisation', 'document-id'];

        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        if (!empty($data['syncStatus'])) {
            $document->setSynchronisationStatus($data['syncStatus']);

            if (in_array($data['syncStatus'], self::DOCUMENT_SYNC_ERROR_STATUSES)) {
                $errorMessage = is_array($data['syncError']) ? json_encode($data['syncError']) : $data['syncError'];
                $document->setSynchronisationError($errorMessage);

                if (Document::SYNC_STATUS_TEMPORARY_ERROR === $data['syncStatus']) {
                    $document->incrementSyncAttempts();
                    $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
                }

                if (Document::SYNC_STATUS_PERMANENT_ERROR === $data['syncStatus'] && $document->getSyncAttempts() >= 3) {
                    $document->setSynchronisationError(self::RETRIES_FAILED_MESSAGE);
                    $document->resetSyncAttempts();
                }
            } else {
                $document->setSynchronisationError(null);
            }

            if (Document::SYNC_STATUS_SUCCESS === $data['syncStatus']) {
                $document->setSynchronisationTime(new \DateTime());
            }
        }

        $this->em->persist($document);
        $this->em->flush();

        return $document;
    }
}