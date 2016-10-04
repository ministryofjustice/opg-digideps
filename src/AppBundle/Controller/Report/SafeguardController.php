<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;
use Symfony\Component\HttpFoundation\Request;

class SafeguardController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/safeguarding/edit", name="safeguarding")
     * @Template()
     */
    public function editAction(Request $request, $reportId)
    {
        $step = $request->get('step', 1);

        $report = $this->getReportIfReportNotSubmitted($reportId, ['safeguarding']);
        if ($report->getSafeguarding() == null) {
            $safeguarding = new EntityDir\Report\Safeguarding();
        } else {
            $safeguarding = $report->getSafeguarding();
//            if ($step==1) {
//                return $this->redirectToRoute('safeguarding_review', ['reportId' => $reportId]);
//            }
        }


        $form = $this->createForm(new FormDir\SafeguardingType($step), $safeguarding);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantSafeguardingData();

            if ($safeguarding->getId() == null) {
                $this->getRestClient()->post('report/safeguarding', $data, ['safeguarding', 'report-id']);
            } else {
                $this->getRestClient()->put('report/safeguarding/'.$safeguarding->getId(), $data, ['safeguarding']);
            }

            //$t = $this->get('translator')->trans('page.safeguardinfoSaved', [], 'report-safeguarding');
            //$this->get('session')->getFlashBag()->add('action', $t);

            if ($step == 4) {
                return $this->redirectToRoute('safeguarding_review', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('safeguarding', ['reportId' => $reportId, 'step'=>$step + 1]);
        }

        $reportStatusService = new ReportStatusService($report);

        return['report' => $report,
                'step' => $step,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/safeguarding/review", name="safeguarding_review")
     * @Template()
     */
    public function reviewAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['safeguarding']);

        return[
            'report' => $report,
        ];
    }
}
