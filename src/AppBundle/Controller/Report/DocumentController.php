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
        $document->setIsReportPdf($data['is_report_pdf']);
        $this->persistAndFlush($document);

        return ['id' => $document->getId()];
    }

    /**
     * GET document by id
     *
     * @Route("/document/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['documents'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        /* @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);

        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        return $document;
    }


    /**
     * Delete document.
     *
     * @Method({"DELETE"})
     *
     * @Route("/document/{id}")
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        $this->get('logger')->debug('Deleting document ' . $id);

        try {

            /** @var $document EntityDir\Report\Document */
            $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);

            // enable if the check above is removed and the note is available for editing for the whole team
            $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

            $this->getEntityManager()->remove($document);

            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->get('logger')->error('Failed to delete document ID: ' . $id . ' - ' . $e->getMessage());
        }

        return [];
    }

    /**
     * GET soft-documents
     *
     * @Route("/document/soft-deleted")
     * @Method({"GET"})
     */
    public function getSoftDeletedDocuments(Request $request)
    {
        if (!$this->getAuthService()->isSecretValidForRole(EntityDir\User::ROLE_ADMIN, $request)) {
            throw new \RuntimeException('Endpoint only accessible from ADMIN container.', 403);
        }

        $this->setJmsSerialiserGroups(['document-id', 'document-storage-reference']);

        return $this->getRepository(EntityDir\Report\Document::class)
            ->retrieveSoftDeleted();
    }

}
