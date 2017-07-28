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
        $this->persistAndFlush($document);

        return ['id' => $document->getId()];
    }

    /**
     * GET document by id
     *
     * @Route("/document/{id}")
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
            /** @var $document EntityDir\Report\Document $note */
            $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);

            // enable if the check above is removed and the note is available for editing for the whole team
            $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

            $this->getEntityManager()->remove($document);

            $this->getEntityManager()->flush($document);
        } catch (\Exception $e) {
            $this->get('logger')->error('Failed to delete document ID: ' . $id . ' - ' . $e->getMessage());
        }

        return [];
    }
}
