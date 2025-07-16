<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Service\Client\RestClient;
use App\Service\DocumentDownloader;
use App\Service\File\Storage\S3Storage;
use App\Service\ParameterStoreService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class ReportSubmissionController extends AbstractController
{
    public const ACTION_DOWNLOAD = 'download';
    public const ACTION_ARCHIVE = 'archive';
    public const ACTION_SYNCHRONISE = 'synchronise';

    public function __construct(
        private readonly DocumentDownloader $documentDownloader,
        private readonly S3Storage $s3Storage,
        private readonly TranslatorInterface $translator,
        private readonly RestClient $restClient,
    ) {
    }

    /**
     * @Route("/documents/list", methods={"GET", "POST"}, name="admin_documents")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/ReportSubmission/index.html.twig")
     *
     * @return array<mixed>|Response
     */
    public function indexAction(Request $request, ParameterStoreService $parameterStoreService)
    {
        if ($request->isMethod('POST')) {
            $ret = $this->processPost($request);

            if ($ret instanceof Response) {
                return $ret;
            }
        }

        $currentFilters = self::getFiltersFromRequest($request);
        $ret = $this->restClient->get('/report-submission?'.http_build_query($currentFilters), 'array');
        $records = $this->restClient->arrayToEntities(EntityDir\Report\ReportSubmission::class.'[]', $ret['records']);

        $nOfdownloadableSubmissions = count(array_filter($records, function ($s) {
            return $s->isDownloadable();
        }));

        if ('archived' === $currentFilters['status']) {
            $postActions = [self::ACTION_DOWNLOAD];
        } else {
            $postActions = [self::ACTION_DOWNLOAD, self::ACTION_ARCHIVE];
        }

        /** @var EntityDir\User $user */
        $user = $this->getUser();

        $isDocumentSyncEnabled = $parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC);

        if ('1' === $isDocumentSyncEnabled && EntityDir\User::ROLE_SUPER_ADMIN === $user->getRoleName()) {
            $postActions[] = self::ACTION_SYNCHRONISE;
        }

        return [
            'filters' => $currentFilters,
            'records' => $records,
            'postActions' => $postActions,
            'counts' => [
                'new' => $ret['counts']['new'],
                'pending' => $ret['counts']['pending'],
                'archived' => $ret['counts']['archived'],
            ],
            'nOfdownloadableSubmissions' => $nOfdownloadableSubmissions,
            'currentTab' => $currentFilters['status'],
            'isDocumentSyncEnabled' => $isDocumentSyncEnabled,
        ];
    }

    /**
     * @Route("/documents/list/download", methods={"GET"}, name="admin_documents_download")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function downloadDocuments(Request $request): Response
    {
        $reportSubmissionIds =
            !empty($request->query->get('reportSubmissionIds')) ? json_decode(urldecode($request->query->get('reportSubmissionIds'))) : null;

        $downloadLocation = '';

        if (!empty($reportSubmissionIds)) {
            try {
                [$retrievedDocuments, $missingDocuments] = $this->documentDownloader->retrieveDocumentsFromS3ByReportSubmissionIds($request, $reportSubmissionIds);
                $downloadLocation = $this->documentDownloader->zipDownloadedDocuments($retrievedDocuments);
            } catch (\Throwable $e) {
                $this->addFlash('error', 'There was an error downloading the requested documents: '.$e->getMessage());

                return $this->redirectToRoute('admin_documents_download_ready');
            }
        }

        $response = new BinaryFileResponse($downloadLocation);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * @Route("/documents/{submissionId}/{documentId}/download", methods={"GET"}, name="admin_document_download")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function downloadIndividualDocument(int $submissionId, int $documentId): Response
    {
        $client = $this->restClient;

        /** @var EntityDir\Report\ReportSubmission $submission */
        $submission = $client->get("report-submission/{$submissionId}", 'Report\\ReportSubmission');

        $documents = array_values(array_filter($submission->getDocuments(), function ($document) use ($documentId) {
            return $document->getId() === $documentId;
        }));

        if (1 !== count($documents)) {
            throw $this->createNotFoundException('Document not found');
        }

        /** @var EntityDir\Report\Document $document */
        $document = $documents[0];

        try {
            $contents = $this->s3Storage->retrieve($document->getStorageReference());
        } catch (\Throwable $e) {
            $filename = $document->getFileName();
            throw $this->createNotFoundException("Document '$filename' could not be retrieved");
        }

        $response = new Response($contents);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$document->getFileName().'"');
        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/documents/list/download_ready", methods={"GET"}, name="admin_documents_download_ready")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/ReportSubmission/download-ready.html.twig")
     *
     * @return array<mixed>
     */
    public function downloadReady(Request $request)
    {
        return ['reportSubmissionIds' => $request->query->get('reportSubmissionIds')];
    }

    /**
     * Process a post.
     *
     * @param Request $request request
     *
     * @return Response|void
     */
    private function processPost(Request $request)
    {
        $checkedBoxes = $request->request->all('checkboxes');

        if (empty($checkedBoxes)) {
            $this->addFlash('error', 'Please select at least one report submission');

            return;
        }

        $checkedBoxes = array_keys($checkedBoxes);
        $action = is_null($request->request->get('multiAction')) ? null : strtolower($request->request->get('multiAction'));

        if (in_array($action, [self::ACTION_DOWNLOAD, self::ACTION_ARCHIVE, self::ACTION_SYNCHRONISE])) {
            $totalChecked = count($checkedBoxes);

            switch ($action) {
                case self::ACTION_ARCHIVE:
                    $this->processArchive($checkedBoxes);
                    $transKey = $totalChecked > 1 ? 'page.postactions.archived.noticePlural' : 'page.postactions.archived.noticeSingular';
                    $notice = $this->translator->trans(
                        $transKey,
                        [
                            'count' => $totalChecked,
                        ],
                        'admin-documents'
                    );

                    $this->addFlash('notice', $notice);
                    break;

                case self::ACTION_DOWNLOAD:
                    try {
                        [$retrievedDocuments, $missingDocuments] = $this->documentDownloader->retrieveDocumentsFromS3ByReportSubmissionIds($request, $checkedBoxes);

                        if (!empty($missingDocuments)) {
                            $this->documentDownloader->setMissingDocsFlashMessage($request, $missingDocuments);

                            return $this->redirectToRoute('admin_documents_download_ready', ['reportSubmissionIds' => json_encode($checkedBoxes)]);
                        }

                        $fileName = $this->documentDownloader->zipDownloadedDocuments($retrievedDocuments);

                        return $this->documentDownloader->generateDownloadResponse($fileName);
                    } catch (\Throwable $e) {
                        $this->addFlash('error', 'There was an error downloading the requested documents: '.$e->getMessage());

                        return $this->redirectToRoute('admin_documents');
                    }

                case self::ACTION_SYNCHRONISE:
                    foreach ($checkedBoxes as $reportSubmissionId) {
                        $this->restClient->put("report-submission/{$reportSubmissionId}/queue-documents", []);
                    }
            }
        }
    }

    /**
     * Archive multiple documents based on the supplied ids.
     *
     * @param array<int, int|string> $checkedBoxes ids selected by the user
     */
    private function processArchive($checkedBoxes): void
    {
        foreach ($checkedBoxes as $reportSubmissionId) {
            $this->restClient->put("report-submission/{$reportSubmissionId}", ['archive' => true]);
        }
    }

    /**
     * @return array<mixed>
     */
    private static function getFiltersFromRequest(Request $request)
    {
        $order = 'new' === $request->get('status', 'new') ? 'DESC' : 'ASC';

        return [
            'q' => $request->get('q'),
            'status' => $request->get('status', 'pending'), // new | archived
            'limit' => $request->query->get('limit') ?: 15,
            'offset' => $request->query->get('offset') ?: 0,
            'created_by_role' => $request->get('created_by_role'),
            'orderBy' => $request->get('orderBy', 'createdOn'),
            'order' => $request->get('order', $order),
            'fromDate' => $request->get('fromDate'),
        ];
    }
}
