<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use AppBundle\Service\SectionValidator\ActionsValidator;
use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ActionController extends AbstractController
{
    private static $jmsGroups = [
        'action',
    ];

    /**
     * @Route("/report/{reportId}/actions", name="actions")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ((new ReportStatusService($report))->getActionsState()['state'] != ReportStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/step/{step}", name="actions_step")
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $action = $report->getAction() ?: new EntityDir\Report\Action();
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('actions', 'actions_step', 'actions_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(new FormDir\Report\ActionType($step, $this->get('translator'), $report->getClient()->getFirstname()), $action);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Action */
            $data->setReport($report);

            $this->getRestClient()->put('report/' . $reportId . '/action', $data);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report'       => $report,
            'step'         => $step,
            'reportStatus' => new ReportStatusService($report),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/summary", name="actions_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        //$this->flagSectionStarted($report, self::SECTION_ID);
        if ((new ReportStatusService($report))->getActionsState()['state'] == ReportStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('actions', ['reportId' => $reportId]);
        }

        if (!$report->getAction()) { //allow validation with answers all skipped
            $report->setAction(new EntityDir\Report\Action());
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report'             => $report,
            'validator'          => new ActionsValidator($report->getAction()),
        ];
    }
}
