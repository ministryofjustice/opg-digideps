<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\StepRedirector;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ActionController extends AbstractController
{
    private static $jmsGroups = [
        'action',
        'action-state',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    )
    {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("/report/{reportId}/actions", name="actions")
     * @Template("AppBundle:Report/Action:start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getActionsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/step/{step}", name="actions_step")
     * @Template("AppBundle:Report/Action:step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $action = $report->getAction() ?: new EntityDir\Report\Action();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('actions', 'actions_step', 'actions_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\ActionType::class, $action, [ 'step'            => $step, 'translator'      => $this->get('translator'), 'clientFirstName' => $report->getClient()->getFirstname()
                                   ]
                                 );
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Action */
            $data->setReport($report);

            $this->restClient->put('report/' . $reportId . '/action', $data);

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
            'reportStatus' => $report->getStatus(),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/summary", name="actions_summary")
     * @Template("AppBundle:Report/Action:summary.html.twig")
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getActionsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('actions', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report'             => $report,
            'status'             => $report->getStatus()
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'actions';
    }
}
