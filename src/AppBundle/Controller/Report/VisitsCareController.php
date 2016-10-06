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
     * @Route("/report/{reportId}/visits-care/start", name="visits_care")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['visits-care']);
        if($report->getVisitsCare() != null) {
            return $this->redirectToRoute('visits_care_review', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/visits-care/step/{step}", name="visits_care_step")
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['visits-care']);
        $visitsCare = $report->getVisitsCare() ?: new EntityDir\Report\VisitsCare();

        $form = $this->createForm(new FormDir\Report\VisitsCareType($step), $visitsCare);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() == null) {
                $this->getRestClient()->post('report/visits-care', $data, ['visits-care', 'report-id']);
            } else {
                $this->getRestClient()->put('report/visits-care/' . $visitsCare->getId(), $data, ['visits-care']);
            }

            if ($step == 4) {
                return $this->redirectToRoute('visits_care_review', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('visits_care_step', ['reportId' => $reportId, 'step' => $step + 1]);
        }

        $reportStatusService = new ReportStatusService($report);

        return [
            'report' => $report,
            'step' => $step,
            'reportStatus' => $reportStatusService,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/visits-care/review", name="visits_care_review")
     * @Template()
     */
    public function reviewAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['visits-care']);
        if (!$report->getVisitsCare()) {
            return $this->redirectToRoute('visits_care', ['reportId' => $reportId]);
        }


        return [
            'report' => $report,
        ];
    }
}
