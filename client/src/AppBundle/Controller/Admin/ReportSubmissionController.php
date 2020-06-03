<?php declare(strict_types=1);

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\DocumentDownloader;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\ParameterStoreService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Throwable;

/**
 * @Route("/admin")
 */
class ReportSubmissionController extends AbstractController
{
    const ACTION_DOWNLOAD = 'download';
    const ACTION_ARCHIVE = 'archive';
    const ACTION_SYNCHRONISE = 'synchronise';

    /**
     * @var DocumentDownloader
     */
    private $documentDownloader;

    /**
     * @var S3Storage
     */
    private $s3Storage;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(DocumentDownloader $documentDownloader, S3Storage $s3Storage, TranslatorInterface $translator)
    {
        $this->documentDownloader = $documentDownloader;
        $this->s3Storage = $s3Storage;
        $this->translator = $translator;
    }

    /**
     * @Route("/documents/list", name="admin_documents", methods={"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/ReportSubmission:index.html.twig")
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
        $ret = $this->getRestClient()->get('/report-submission?' . http_build_query($currentFilters), 'array');

        $records = $this->getRestClient()->arrayToEntities(EntityDir\Report\ReportSubmission::class . '[]', $ret['records']);

        $nOfdownloadableSubmissions = count(array_filter($records, function ($s) {
            return $s->isDownloadable();
        }));

        if ($currentFilters['status'] === 'archived') {
            $postActions = [self::ACTION_DOWNLOAD];
        } else {
            $postActions = [self::ACTION_DOWNLOAD, self::ACTION_ARCHIVE];
        }

        /** @var EntityDir\User $user */
        $user = $this->getUser();

        if ($parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC) === '1' && $user->getRoleName() === EntityDir\User::ROLE_SUPER_ADMIN) {
            $postActions[] = self::ACTION_SYNCHRONISE;
        }

        return [
            'filters' => $currentFilters,
            'records' => $records,
            'postActions' => $postActions,
            'counts'  => [
                'new'      => $ret['counts']['new'],
                'pending'  => $ret['counts']['pending'],
                'archived' => $ret['counts']['archived'],
            ],
            'nOfdownloadableSubmissions' => $nOfdownloadableSubmissions,
            'currentTab' => $currentFilters['status'],
        ];
    }

    /**
     * @Route("/documents/list/download", name="admin_documents_download", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
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
            } catch(Throwable $e) {
                $this->addFlash('error', 'There was an error downloading the requested documents: ' . $e->getMessage());
                return $this->redirectToRoute('admin_documents_download_ready');
            }
        }

        $response = new BinaryFileResponse($downloadLocation);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }

    /**
     * @Route("/documents/{submissionId}/{documentId}/download", name="admin_document_download", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function downloadIndividualDocument(int $submissionId, int $documentId): Response
    {
        $client = $this->getRestClient();

        /** @var EntityDir\Report\ReportSubmission $submission */
        $submission = $client->get("report-submission/{$submissionId}", 'Report\\ReportSubmission');

        $documents = array_values(array_filter($submission->getDocuments(), function ($document) use ($documentId) {
            return $document->getId() === $documentId;
        }));

        if (count($documents) !== 1) {
            throw $this->createNotFoundException('Document not found');
        }

        /** @var EntityDir\Report\Document $document */
        $document = $documents[0];

        try {
            $contents = $this->s3Storage->retrieve($document->getStorageReference());
        } catch (Throwable $e) {
            $filename = $document->getFileName();
            throw $this->createNotFoundException("Document '${$filename}' could not be retrieved");
        }

        $response = new Response($contents);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $document->getFileName() . '"');
        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/documents/list/download_ready", name="admin_documents_download_ready", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/ReportSubmission:download-ready.html.twig")
     *
     * @return array<mixed>
     */
    public function downloadReady(Request $request)
    {
        return ['reportSubmissionIds' => $request->query->get('reportSubmissionIds')];
    }

    /**
     * Process a post
     *
     * @param Request $request request
     * @return Response|void
     */
    private function processPost(Request $request)
    {
        if (empty($request->request->get('checkboxes'))) {
            $this->addFlash('error', 'Please select at least one report submission');
            return;
        }

        $checkedBoxes = array_keys($request->request->get('checkboxes'));
        $action = strtolower($request->request->get('multiAction'));

        if (in_array($action, [self::ACTION_DOWNLOAD, self::ACTION_ARCHIVE, self::ACTION_SYNCHRONISE])) {
            $totalChecked = count($checkedBoxes);

            switch ($action) {
                case self::ACTION_ARCHIVE:
                    $this->processArchive($checkedBoxes);
                    $notice = $this->translator->transChoice(
                        'page.postactions.archived.notice',
                        $totalChecked,
                        ['%count%' => $totalChecked],
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
                    } catch (Throwable $e) {
                        $this->addFlash('error', 'There was an error downloading the requested documents: ' . $e->getMessage());
                        return $this->redirectToRoute('admin_documents');
                    }

                case self::ACTION_SYNCHRONISE:
                    foreach ($checkedBoxes as $reportSubmissionId) {
                        $this->getRestClient()->put("report-submission/{$reportSubmissionId}/queue-documents", []);
                    }
            }
        }
    }

    /**
     * Archive multiple documents based on the supplied ids
     *
     * @param array<int, int|string> $checkedBoxes ids selected by the user
     *
     */
    private function processArchive($checkedBoxes): void
    {
        foreach ($checkedBoxes as $reportSubmissionId) {
            $this->getRestClient()->put("report-submission/{$reportSubmissionId}", ['archive'=>true]);
        }
    }

    /**
     * @param  Request $request
     * @return array<mixed>
     */
    private static function getFiltersFromRequest(Request $request)
    {
        $order = $request->get('status', 'new') === 'new' ? 'ASC' : 'DESC';

        return [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new'), // new | archived
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
            'created_by_role'   => $request->get('created_by_role'),
            'orderBy'           => $request->get('orderBy', 'createdOn'),
            'order'             => $request->get('order', $order),
            'fromDate'          => $request->get('fromDate')
        ];
    }
}
