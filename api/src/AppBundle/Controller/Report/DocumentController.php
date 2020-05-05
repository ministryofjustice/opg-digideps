<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Document;
use AppBundle\Exception\UnauthorisedException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends RestController
{
    const DOCUMENT_SYNC_ERROR_STATUSES = [Document::SYNC_STATUS_TEMPORARY_ERROR, Document::SYNC_STATUS_PERMANENT_ERROR];

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

    /**
     * Get queued documents
     *
     * @Route("/document/queued", methods={"GET"})
     *
     * @return string
     */
    public function getQueuedDocuments(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = $this->deserializeBodyContent($request);

        $documentRepo = $em->getRepository(Document::class);

        return json_encode($documentRepo->getQueuedDocumentsAndSetToInProgress($data['row_limit']));
    }

    /**
     * Get queued documents
     *
     * @Route("/document/update-related-statuses", methods={"PUT"})
     *
     * @return string
     */
    public function updateRelatedDocumentStatuses(Request $request, EntityManagerInterface $em): string
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $documentRepo = $em->getRepository(Document::class);

        $data = json_decode($request->getContent(), true);
        $reportSubmissionIds = $data['submissionIds'];
        $errorMessage = $data['errorMessage'];

        $count = $documentRepo->updateSupportingDocumentStatusByReportSubmissionIds($reportSubmissionIds, $errorMessage);

        return json_encode($count);
    }

    /**
     * Update a Document
     *
     * @Route("/document/{id}", methods={"PUT"})
     *
     * @return Document
     */
    public function update(Request $request, int $id, EntityManagerInterface $em): Document
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new UnauthorisedException('client secret not accepted.');
        }

        $data = $this->deserializeBodyContent($request);

        $document = $em->getRepository(Document::class)->find($id);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['document-synchronisation', 'document-id'];

        $this->setJmsSerialiserGroups($serialisedGroups);

        if (!empty($data['syncStatus'])) {
            $document->setSynchronisationStatus($data['syncStatus']);

            if (in_array($data['syncStatus'], self::DOCUMENT_SYNC_ERROR_STATUSES)) {
                $errorMessage = is_array($data['syncError']) ? json_encode($data['syncError']) : $data['syncError'];
                $document->setSynchronisationError($errorMessage);
            } else {
                $document->setSynchronisationError(null);
            }

            if ($data['syncStatus'] === Document::SYNC_STATUS_SUCCESS) {
                $document->setSynchronisationTime(new DateTime());
            }
        }

        $this->persistAndFlush($document);

        return $document;
    }
}
