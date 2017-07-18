<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends RestController
{
    /**
     * Get list of reports
     * ADMIN user only, to get reports along with documents
     *
     * @Route("/document/get-all-with-reports")
     * @Method({"GET"})
     */
    public function getSubmitted(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN]);

        $qb = $this->getRepository(EntityDir\Report\Report::class)->createQueryBuilder('r');
        $qb
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            //->where('r.submitted = true') //ENABLE ME. disabled only for faster tsting on develop-master-2
            ->orderBy('r.submittedBy', 'DESC')
        ;

        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['report'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $qb->getQuery()->getResult();
    }

    /**
     * @Route("/report/{reportId}/document", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        /* @var $report Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        // hydrate and persist
        $data = $this->deserializeBodyContent($request, [
            'file_name' => 'notEmpty',
            'storage_reference' => 'notEmpty'
        ]);
        $document = new EntityDir\Report\Document($report);
        $document->setCreatedBy($this->getUser());
        $document->setFileName($data['file_name']);
        $document->setStorageReference($data['storage_reference']);
        $this->persistAndFlush($document);

        return ['id' => $document->getId()];
    }

    /**
     * Get document by ID
     * Used to get the stroage reference, for downloading
     *
     * @Route("/document/{documentId}", requirements={"documentId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $documentId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        /* @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $documentId);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['documents', 'document-storage-reference'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $document;
    }

    /**
     * Hard delete for admin users
     *
     * @Route("/document/{documentId}", requirements={"documentId":"\d+"})
     * @Method({"DELETE"})
     */
    public function deleteOneById(Request $request, $documentId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        /* @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $documentId);

        $this->getEntityManager()->remove($document);
        $this->getEntityManager()->flush($document);

        return [];
    }
}
