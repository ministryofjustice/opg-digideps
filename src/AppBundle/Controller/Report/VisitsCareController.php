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
     * @Route("/report/{reportId}/visits-care", name="visits_care")
     * @Template()
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['visits-care']);
        if ($report->getVisitsCare() == null) {
            $visitsCare = new EntityDir\Report\VisitsCare();
        } else {
            $visitsCare = $report->getVisitsCare();
        }

        $form = $this->createForm(new FormDir\Report\VisitsCareType(), $visitsCare);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() == null) {
                $this->getRestClient()->post('report/visits-care', $data, ['visits-care', 'report-id']);
            } else {
                $this->getRestClient()->put('report/visits-care/'.$visitsCare->getId(), $data, ['visits-care']);
            }

            return $this->redirect($this->generateUrl('visits_care', ['reportId' => $reportId]).'#pageBody');
        }

        $reportStatusService = new ReportStatusService($report);

        return['report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }
}
