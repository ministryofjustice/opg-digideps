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

    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_UPLOAD]);

        // use archived flag if existing. default to false
        $archived = 'false';
        if ($request->get('archived', null)) {
            $archived = $request->get('archived') ? 'true' : 'false';
        }

        $qb = $this->getRepository(EntityDir\Report\ReportSubmission::class)->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->join('rs.documents', 'd')
            ->where('rs.archived = '.$archived)
            //->where('r.submitted = true') //ENABLE ME. disabled only for faster tsting on develop-master-2
            ->orderBy('rs.id', 'DESC')
        ;

        // groups not customisable, to reduce risk of API accessing too much data
        $this->setJmsSerialiserGroups([
            'report-submission',
            'report-submission-report',
            'report', 'client', //
            'documents', //storage ref etc..
            'report-submission-archived-by'
        ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DOCUMENT_UPLOAD]);

        // use archived flag if existing. default to false
        $archived = 'false';
        if ($request->get('archived', null)) {
//            $archived = $request->get('archived') ? 'true' : 'false';
        }

        $ret = $this->getRepository(EntityDir\Report\ReportSubmission::class)->find($id);

        // groups not customisable, to reduce risk of API accessing too much data
        $this->setJmsSerialiserGroups([
            'report-submission',
            'report-submission-report',
            'report', 'client', //
            'documents',
            'document-storage-reference'
        ]);

        return $ret;
    }


    /**
     * Archive documents
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
