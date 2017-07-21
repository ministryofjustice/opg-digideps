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
        'report', 'client',
        'documents'
    ];

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_UPLOAD]);

        // use archived flag if existing. default to false
        $archived = $request->get('archived', false);

        $qb = $this->getRepository(EntityDir\Report\ReportSubmission::class)->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->join('rs.documents', 'd')
            ->where('rs.archived = ' . ($archived ? 'true' : 'false') )
            ->orderBy('rs.id', 'DESC')
        ;

        $this->setJmsSerialiserGroups(self::$jmsGroups);

        return $qb->getQuery()->getResult();
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_UPLOAD]);

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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DOCUMENT_UPLOAD);

        /* @var $reportSubmission EntityDir\Report\ReportSubmission */
        $reportSubmission = $this->findEntityBy(EntityDir\Report\ReportSubmission::class, $reportSubmissionId);
        $reportSubmission->setArchived(true);
        $reportSubmission->setArchivedBy($this->getUser());
        $ret = [];
        foreach($reportSubmission->getDocuments() as $document) {
            $ret[] = $document->getStorageReference();
        }
        $this->getEntityManager()->flush();

        return $ret;
    }
}
