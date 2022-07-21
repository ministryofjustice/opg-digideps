<?php

namespace App\Controller\Synchronisation;

use App\Controller\AbstractController;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\RestClient;
use App\Service\DocumentSyncService;
use App\Service\ParameterStoreService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentSynchronisationController extends AbstractController
{
    public const FALLBACK_ROW_LIMITS = '100';
    public const COMPLETED_MESSAGE = 'Sync command completed';

    public static $defaultName = 'digideps:document-sync';

    /** @var DocumentSyncService */
    private $documentSyncService;

    /** @var RestClient */
    private $restClient;

    /** @var SerializerInterface */
    private $serializer;

    /** @var ParameterStoreService */
    private $parameterStore;

    private LoggerInterface $logger;

    public function __construct(
        DocumentSyncService $documentSyncService,
        RestClient $restClient,
        SerializerInterface $serializer,
        ParameterStoreService $parameterStore,
        LoggerInterface $logger
    ) {
        $this->documentSyncService = $documentSyncService;
        $this->restClient = $restClient;
        $this->serializer = $serializer;
        $this->parameterStore = $parameterStore;
        $this->logger = $logger;
    }

    /**
     * @Route("/synchronise/jim", name="jim", methods={"GET"})
     */
    public function jim(): JsonResponse
    {
        $validJWT = $this->restClient->apiCall(
            'get',
            'authorise/jwt',
            [],
            'raw',
            [],
            false
        );

        return new JsonResponse([$validJWT]);
    }

    /**
     * @Route("/synchronise/documents", name="synchronise_documents", methods={"POST"})
     */
    public function synchroniseDocument(): JsonResponse
    {
        ini_set('memory_limit', '512M');

        if (!$this->isFeatureEnabled()) {
            return new JsonResponse(['Document Sync Disabled']);
        }

        /** @var QueuedDocumentData[] $documents */
        $documents = $this->getQueuedDocumentsData();

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

    private function isFeatureEnabled(): bool
    {
        return '1' === $this->parameterStore->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC);
    }

    private function getSyncRowLimit(): string
    {
        $limit = $this->parameterStore->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT);

        return $limit ? $limit : self::FALLBACK_ROW_LIMITS;
    }

    /**
     * @return QueuedDocumentData[]
     */
    private function getQueuedDocumentsData(): array
    {
        $queuedDocumentData = $this->restClient->apiCall(
            'get',
            'document/queued',
            ['row_limit' => $this->getSyncRowLimit()],
            'array',
            [],
            false
        );

        return $this->serializer->deserialize($queuedDocumentData, 'App\Model\Sirius\QueuedDocumentData[]', 'json');
    }
}
