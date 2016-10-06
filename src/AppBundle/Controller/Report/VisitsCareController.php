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
    const STEPS = 4;

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
        $comingFromReviewPage = $request->get('from') === 'review';

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

            // return to review if coming from review, or it's the last step
            if ($comingFromReviewPage) {
                return $this->redirectToRoute('visits_care_review', ['reportId' => $reportId, 'stepEdited'=>$step]);
            }
            if ($step == self::STEPS) {
                return $this->redirectToRoute('visits_care_review', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('visits_care_step', ['reportId' => $reportId, 'step' => $step + 1]);
        }

        $backLink = null;
        if ($comingFromReviewPage) {
            $backLink = $this->generateUrl('visits_care_review', ['reportId' => $reportId]);
        } else if ($step == 1) {
            $backLink = $this->generateUrl('visits_care', ['reportId' => $reportId]);
        } else { // step > 1
            $backLink = $this->generateUrl('visits_care_step', ['reportId' => $reportId, 'step' => $step - 1]);
        }

        return [
            'report' => $report,
            'step' => $step,
            'reportStatus' => new ReportStatusService($report),
            'form' => $form->createView(),
            'backLink' => $backLink,
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
            'stepEdited' => $request->get('stepEdited')
        ];
    }
}
