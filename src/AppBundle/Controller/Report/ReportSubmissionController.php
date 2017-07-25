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
        'report-client',
        'client-name',
        'client-case-number',
        'user-name',
        'documents'
    ];

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_MANAGE]);

        $this->setJmsSerialiserGroups(self::$jmsGroups);

        $archived = $request->get('archived', false);

        return $this->getRepository(EntityDir\Report\ReportSubmission::class)
            ->getReportSubmissions($archived);
    }

    /**
     * @Route("/{id}")
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
     * Archive documents
     * return array of storage references, for admin area to delete if needed
     *
     * @Route("/{reportSubmissionId}/archive", requirements={"reportSubmissionId":"\d+"})
     * @Method({"PUT"})
     */
    public function archive(Request $request, $reportSubmissionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DOCUMENT_MANAGE);

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->findEntityBy(EntityDir\Report\ReportSubmission::class, $reportSubmissionId);
        $reportSubmission->setArchivedBy($this->getUser());
        $ret = [];
        foreach($reportSubmission->getDocuments() as $document) {
            $ret[] = $document->getStorageReference();
        }
        $this->getEntityManager()->flush();

        return $ret;
    }
}
