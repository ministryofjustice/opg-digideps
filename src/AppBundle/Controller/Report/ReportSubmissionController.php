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

        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 15);
        $status = $request->get('status');

        $qb = $this->getRepository(EntityDir\Report\ReportSubmission::class)->createQueryBuilder('rs')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');

        // search filter
        // similar to reportController::getAll() filter used by PA dashboard
        $q = $request->get('q', false);
        if ($q) {
            $qb->andWhere(implode(' OR ', [
                // user
                'lower(cb.firstname) LIKE :qLike',
                'lower(cb.lastname) LIKE :qLike',
                // client names and case number (exact match)
                'lower(c.firstname) LIKE :qLike',
                'lower(c.lastname) LIKE :qLike',
                // case number
                'c.caseNumber = :q'
            ]));
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }

        $records = $qb->getQuery()->getResult(); /* @var $records EntityDir\Report\ReportSubmission[] */

        // calculate total counts, filter based on status, then and apply last limit/offset
        $counts = [
            'new' => 0,
            'archived' => 0,
        ];
        foreach ($records as $record) {
            if ($record->getArchivedBy()) {
                $counts['archived']++;
            } else {
                $counts['new']++;
            }
        }
        $records = array_filter($records, function ($report) use ($status) {
            return ($status === 'new')
                ? ($report->getArchivedBy() === null)
                : ($report->getArchivedBy() !== null);
        });
        $records = array_slice($records, $offset, $limit);

        $this->setJmsSerialiserGroups(self::$jmsGroups);

        return [
            'counts'=>$counts,
            'records'=>$records
        ];
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
