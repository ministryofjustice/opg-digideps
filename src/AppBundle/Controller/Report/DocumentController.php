<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report/")
 */
class DocumentController extends RestController
{
    /**
     * @Route("{reportId}/document", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        /* @var $report Report */
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        // hydrate and persist
        $data = $this->deserializeBodyContent($request, [
            'file_name' => 'notEmpty',
            'storage_reference' => 'notEmpty'
        ]);
        $document = new Document($report);
        $document->setCreatedBy($this->getUser());
        $document->setFileName($data['file_name']);
        $document->setStorageReference($data['storage_reference']);
        $this->persistAndFlush($document);

        return ['id' => $document->getId()];
    }
}
