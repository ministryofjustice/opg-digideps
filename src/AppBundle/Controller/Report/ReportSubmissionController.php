<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report-submission")
 */
class ReportSubmissionController extends RestController
{
    private static $jmsGroups = [
        'report-submission',
        'report-type',
        'report-client',
        'report-period',
        'client-name',
        'client-case-number',
        'user-name',
        'user-rolename',
        'documents'
    ];

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_MANAGE]);
        $repo = $this->getRepository(EntityDir\Report\ReportSubmission::class); /* @var $repo EntityDir\Repository\ReportSubmissionRepository */

        $ret = $repo->findByFiltersWithCounts(
                $request->get('status'),
                $request->get('q'),
                $request->get('created_by_role'),
                $request->get('offset', 0),
                $request->get('limit', 15)
            );

        $this->setJmsSerialiserGroups(self::$jmsGroups);

        return $ret;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_MANAGE]);

        $ret = $this->getRepository(EntityDir\Report\ReportSubmission::class)->find($id);

        $this->setJmsSerialiserGroups(array_merge(self::$jmsGroups, ['document-storage-reference']));

        return $ret;
    }


    /**
     * Update documents
     * return array of storage references, for admin area to delete if needed
     *
     * @Route("/{reportSubmissionId}", requirements={"reportSubmissionId":"\d+"})
     * @Method({"PUT"})
     */
    public function update(Request $request, $reportSubmissionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DOCUMENT_MANAGE);

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->findEntityBy(EntityDir\Report\ReportSubmission::class, $reportSubmissionId);

        $data = $this->deserializeBodyContent($request);
        if (!empty($data['archive'])) {
            $reportSubmission->setArchivedBy($this->getUser());
        }

        $this->getEntityManager()->flush();

        return $reportSubmission->getId();
    }


    /**
     * Get old report submissions.
     * Called from ADMIN cron
     *
     * @Route("/old")
     * @Method({"GET"})
     */
    public function getOld(Request $request)
    {
        if (!$this->getAuthService()->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException(__METHOD__ . ' only accessible from ADMIN container.', 403);
        }

        $repo = $this->getRepository(EntityDir\Report\ReportSubmission::class); /* @var $repo EntityDir\Repository\ReportSubmissionRepository */

        $ret = $repo->findDownloadableOlderThan(new \DateTime(EntityDir\Report\ReportSubmission::REMOVE_FILES_WHEN_OLDER_THAN), 100);

        $this->setJmsSerialiserGroups(['report-submission-id', 'report-submission-documents', 'document-storage-reference']);

        return $ret;
    }

    /**
     * Set report undownloadable (and remove the storage reference for the files.
     * Called from ADMIN cron
     *
     * @Route("/{id}/set-undownloadable", requirements={"id":"\d+"})
     * @Method({"PUT"})
     */
    public function setUndownloadable($id, Request $request)
    {
        if (!$this->getAuthService()->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException(__METHOD__.' only accessible from ADMIN container.', 403);
        }

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->getRepository(EntityDir\Report\ReportSubmission::class)->find($id);
        $reportSubmission->setDownloadable(false);
        foreach($reportSubmission->getDocuments() as $document) {
            $document->setStorageReference(null);
        }

        $this->getEntityManager()->flush();

        return true;
    }
}
