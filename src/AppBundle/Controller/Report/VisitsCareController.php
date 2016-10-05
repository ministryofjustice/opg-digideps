<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/visits-care", name="safeguarding")
     * @Template()
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['safeguarding']);
        if ($report->getSafeguarding() == null) {
            $visitsCare = new EntityDir\Report\Safeguarding();
        } else {
            $visitsCare = $report->getSafeguarding();
        }

        $form = $this->createForm(new FormDir\Report\VisitsCareType(), $visitsCare);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantSafeguardingData();

            if ($visitsCare->getId() == null) {
                $this->getRestClient()->post('report/safeguarding', $data, ['safeguarding', 'report-id']);
            } else {
                $this->getRestClient()->put('report/safeguarding/'.$visitsCare->getId(), $data, ['safeguarding']);
            }

            //$t = $this->get('translator')->trans('page.safeguardinfoSaved', [], 'report-safeguarding');
            //$this->get('session')->getFlashBag()->add('action', $t);

            return $this->redirect($this->generateUrl('safeguarding', ['reportId' => $reportId]).'#pageBody');
        }

        $reportStatusService = new ReportStatusService($report);

        return['report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }
}
