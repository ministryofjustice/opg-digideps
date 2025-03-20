<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\Repository\ReportSubmissionRepository;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report-submission")
 */
class ReportSubmissionController extends RestController
{
    public const QUEUEABLE_STATUSES = [
        null,
        Document::SYNC_STATUS_TEMPORARY_ERROR,
        Document::SYNC_STATUS_PERMANENT_ERROR,
    ];

    private static array $jmsGroups = [
        'report-id',
        'report-submission',
        'report-type',
        'report-client',
        'ndr-client',
        'ndr',
        'report-period',
        'client-name',
        'client-case-number',
        'client-email',
        'client-discharged',
        'user-name',
        'user-rolename',
        'user-teamname',
        'documents',
        'synchronisation',
    ];

    public function __construct(private readonly EntityManagerInterface $em, private readonly AuthService $authService, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getAll(Request $request)
    {
        /** @var ReportSubmissionRepository $repo */
        $repo = $this->em->getRepository(ReportSubmission::class);

        $ret = $repo->findByFiltersWithCounts(
            $request->get('status'),
            $request->get('q'),
            $request->get('created_by_role'),
            $request->get('offset', 0),
            $request->get('limit', 15),
            $request->get('orderBy', 'createdOn'),
            $request->get('order', 'DESC')
        );

        $this->formatter->setJmsSerialiserGroups(self::$jmsGroups);

        return $ret;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getOneById(Request $request, $id)
    {
        $ret = $this->em->getRepository(ReportSubmission::class)->findOneByIdUnfiltered($id);

        $this->formatter->setJmsSerialiserGroups(array_merge(self::$jmsGroups, ['document-storage-reference']));

        return $ret;
    }

    /**
     * Update documents
     * return array of storage references, for admin area to delete if needed.
     *
     * @Route("/{reportSubmissionId}", requirements={"reportSubmissionId":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function update(Request $request, $reportSubmissionId)
    {
        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->findEntityBy(ReportSubmission::class, $reportSubmissionId);

        $data = $this->formatter->deserializeBodyContent($request);

        if (!empty($data['archive'])) {
            $reportSubmission->setArchived(true);
            $reportSubmission->setArchivedBy($this->getUser());
        }

        $this->em->flush();

        return $reportSubmission->getId();
    }

    /**
     * Separating this from update() as it needs to be accessible via client secret which removes the
     * User from the request.
     *
     * @Route("/{reportSubmissionId}/update-uuid", requirements={"reportSubmissionId":"\d+"}, methods={"PUT"})
     */
    public function updateUuid(Request $request, $reportSubmissionId)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->findEntityBy(ReportSubmission::class, $reportSubmissionId);

        $data = $this->formatter->deserializeBodyContent($request);

        if (!empty($data['uuid'])) {
            $reportSubmission->setUuid($data['uuid']);
        }

        $this->em->flush();

        return $reportSubmission->getId();
    }

    /**
     * Get old report submissions.
     * Called from ADMIN cron.
     *
     * @Route("/old", methods={"GET"})
     */
    public function getOld(Request $request)
    {
        if (!$this->authService->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException(__METHOD__.' only accessible from ADMIN container.', 403);
        }

        $repo = $this->em->getRepository(ReportSubmission::class); /* @var $repo EntityDir\Repository\ReportSubmissionRepository */

        $ret = $repo->findDownloadableOlderThan(new \DateTime(ReportSubmission::REMOVE_FILES_WHEN_OLDER_THAN), 100);

        $this->formatter->setJmsSerialiserGroups(['report-submission-id', 'report-submission-documents', 'document-storage-reference']);

        return $ret;
    }

    /**
     * Set report undownloadable (and remove the storage reference for the files.
     * Called from ADMIN cron.
     *
     * @Route("/{id}/set-undownloadable", requirements={"id":"\d+"}, methods={"PUT"})
     */
    public function setUndownloadable($id, Request $request)
    {
        if (!$this->authService->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException(__METHOD__.' only accessible from ADMIN container.', 403);
        }

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->em->getRepository(ReportSubmission::class)->find($id);
        $reportSubmission->setDownloadable(false);
        foreach ($reportSubmission->getDocuments() as $document) {
            $document->setStorageReference(null);
        }

        $this->em->flush();

        return true;
    }

    /**
     * Queue submission documents which have been synced yet.
     *
     * @Route("/{id}/queue-documents", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function queueDocuments($id)
    {
        /** @var ReportSubmission $reportSubmission */
        $reportSubmission = $this->em->getRepository(ReportSubmission::class)->find($id);

        if ($reportSubmission->getArchived()) {
            throw new \InvalidArgumentException('Cannot queue documents for an archived report submission');
        }

        foreach ($reportSubmission->getDocuments() as $document) {
            if (in_array($document->getSynchronisationStatus(), self::QUEUEABLE_STATUSES)) {
                $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
                $document->setSynchronisationError(null);
                $document->setSynchronisedBy($this->getUser());
            }
        }

        $this->em->flush();

        return true;
    }

    /**
     * @Route("/pre-registration-data", name="pre_registration_data", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @throws \Exception
     */
    public function getPreRegistrationData(Request $request): array
    {
        /* @var $repo EntityDir\Repository\ReportSubmissionRepository */
        $repo = $this->em->getRepository(ReportSubmission::class);

        $fromDate = $request->get('fromDate') ? new \DateTime($request->get('fromDate')) : null;
        $toDate = $request->get('toDate') ? new \DateTime($request->get('toDate')) : null;

        $fromDateTime = $fromDate?->setTime(0, 0);
        $toDateTime = $toDate?->setTime(23, 59, 59);

        return $repo->findAllReportSubmissionsRawSql(
            $fromDateTime,
            $toDateTime
        );
    }
}
