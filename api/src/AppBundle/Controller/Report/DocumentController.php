<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_DOCUMENTS];

    /**
     * @Route("/document/{reportType}/{reportId}", requirements={
     *     "reportId":"\d+",
     *     "reportType" = "(report|ndr)"
     * }, methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportType, $reportId)
    {
        if ($reportType === 'report') {
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        } else {
            $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $reportId);
        }

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
        if (!$document->isAdminDocument()) {
            // only set flag to yes if document being added is a deputy Document (and not auto-generated)
            $report->setWishToProvideDocumentation('yes');
        }
        $this->persistAndFlush($document);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $document->getId()];
    }

    /**
     * GET document by id
     *
     * @Route("/document/{id}", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
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
     * Accessible only from deputy area
     *
     * @Route("/document/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     *
     * @return array
     */
    public function delete($id)
    {
        /** @var $document EntityDir\Report\Document */
        $document = $this->findEntityBy(EntityDir\Report\Document::class, $id);
        $report = $document->getReport();

        // enable if the check above is removed and the note is available for editing for the whole team
        $this->denyAccessIfClientDoesNotBelongToUser($document->getReport()->getClient());

        $this->getEntityManager()->remove($document);
        $this->getEntityManager()->flush();

        // update yesno question to null if its the last document to be removed
        if (count($report->getDeputyDocuments()) == 0) {
            $report->setWishToProvideDocumentation(null);
        }

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $id];
    }
}
