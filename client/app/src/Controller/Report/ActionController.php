<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /** @var StepRedirector */
    private $stepRedirector;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi,
        StepRedirector $stepRedirector,
        private ClientApi $clientApi,
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/report/{reportId}/actions", name="actions")
     *
     * @Template("@App/Report/Action/start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = $this->clientApi->checkDeputyHasMultiClients($user);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getActionsState()['state']) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/step/{step}", name="actions_step")
     *
     * @Template("@App/Report/Action/step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step, TranslatorInterface $translator)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('actions_summary', ['reportId' => $reportId]);
        }
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $action = $report->getAction() ?: new EntityDir\Report\Action();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('actions', 'actions_step', 'actions_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(
            FormDir\Report\ActionType::class,
            $action,
            [
                'step' => $step,
                'translator' => $translator,
                'clientFirstName' => $report->getClient()->getFirstname(),
            ]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Action */
            $data->setReport($report);

            $this->restClient->put('report/'.$reportId.'/action', $data);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/actions/summary", name="actions_summary")
     *
     * @Template("@App/Report/Action/summary.html.twig")
     */
    public function summaryAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = $this->clientApi->checkDeputyHasMultiClients($user);

        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getActionsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('actions', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
            'isMultiClientDeputy' => $isMultiClientDeputy,
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
