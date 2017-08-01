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

        $ret = $this->getRepository(EntityDir\Report\ReportSubmission::class)
            ->findByFiltersWithCounts(
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
     * @Route("/{reportSubmissionId}", requirements={"reportSubmissionId":"\d+"})
     * @Method({"PUT"})
     */
    public function archive(Request $request, $reportSubmissionId)
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
}
