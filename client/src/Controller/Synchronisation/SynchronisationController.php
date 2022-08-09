<?php

namespace App\Controller\Synchronisation;

use App\Controller\AbstractController;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\ChecklistSyncService;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\DocumentSyncService;
use App\Service\ParameterStoreService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class SynchronisationController extends AbstractController
{
    public const DOCUMENT_FALLBACK_ROW_LIMITS = '100';
    public const CHECKLIST_FALLBACK_ROW_LIMITS = '30';
    public const COMPLETED_MESSAGE = 'Sync command completed';

    public static $defaultName = 'digideps:document-sync';

    public function __construct(
        private DocumentSyncService $documentSyncService,
        private ChecklistSyncService $checklistSyncService,
        private RestClient $restClient,
        private SerializerInterface $serializer,
        private ParameterStoreService $parameterStore,
        private LoggerInterface $logger,
        private ReportApi $reportApi
    ) {
    }

    /**
     * @Route("/synchronise/documents", name="synchronise_documents", methods={"POST", "GET"})
     */
    public function synchroniseDocument(Request $request): JsonResponse
    {
        if (!$this->isDocumentFeatureEnabled()) {
            return new JsonResponse(['Document Sync Disabled']);
        }

        ini_set('memory_limit', '512M');

        /** @var QueuedDocumentData[] $documents */
        $documents = $this->getQueuedDocumentsData($request);

        $this->logger->info(sprintf('%d documents to upload', count($documents)));

        foreach ($documents as $document) {
            $this->documentSyncService->syncDocument($document);
        }

        if (count($this->documentSyncService->getSyncErrorSubmissionIds()) > 0) {
            $this->documentSyncService->setSubmissionsDocumentsToPermanentError();
            $this->documentSyncService->setSyncErrorSubmissionIds([]);
        }

        if ($this->documentSyncService->getDocsNotSyncedCount() > 0) {
            $this->logger->info(sprintf('%d documents failed to sync', $this->documentSyncService->getDocsNotSyncedCount()));
            $this->documentSyncService->setDocsNotSyncedCount(0);
        }

        return new JsonResponse([self::COMPLETED_MESSAGE]);
    }

    /**
     * @Route("/synchronise/checklists", name="synchronise_checklists", methods={"POST", "GET"})
     */
    public function synchroniseChecklist(Request $request): JsonResponse
    {
        if (!$this->isChecklistFeatureEnabled()) {
            return new JsonResponse(['Checklist Sync Disabled']);
        }

        ini_set('memory_limit', '512M');

        $rowLimit = $this->getChecklistSyncRowLimit();

        /** @var array $reports */
        $reports = $this->reportApi->getReportsWithQueuedChecklistsAPI($request, $rowLimit);
        $this->logger->info(sprintf('%d checklists to upload', count($reports)));

        $notSyncedCount = $this->checklistSyncService->syncChecklistsByReports($reports);

        if ($notSyncedCount > 0) {
            $this->logger->info(sprintf('%d checklists failed to sync', $notSyncedCount));
        }

        $this->logger->info(self::COMPLETED_MESSAGE);

        return new JsonResponse([self::COMPLETED_MESSAGE]);
    }

    private function isDocumentFeatureEnabled(): bool
    {
        return '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC);
    }

    private function isChecklistFeatureEnabled(): bool
    {
        return '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC);
    }

    private function getDocumentSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);

        return $limit ? $limit : self::DOCUMENT_FALLBACK_ROW_LIMITS;
    }

    private function getChecklistSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT);

        return $limit ? $limit : self::CHECKLIST_FALLBACK_ROW_LIMITS;
    }

    /**
     * @return QueuedDocumentData[]
     */
    private function getQueuedDocumentsData(Request $request): array
    {
        $queuedDocumentData = $this->restClient->apiCall(
            'get',
            'document/queued',
            ['row_limit' => $this->getDocumentSyncRowLimit()],
            'array',
            [
                'headers' => [
                    'JWT' => $request->headers->get('JWT'),
                ],
            ],
            false
        );

        return $this->serializer->deserialize($queuedDocumentData, 'App\Model\Sirius\QueuedDocumentData[]', 'json');
    }
}
