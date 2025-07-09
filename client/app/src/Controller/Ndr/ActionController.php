<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActionController extends AbstractController
{
    private static $jmsGroups = [
        'ndr-action-give-gifts',
        'ndr-action-property',
        'ndr-action-more-info',
    ];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly RestClient $restClient,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    /**
     * @Route("/ndr/{ndrId}/actions", name="ndr_actions")
     *
     * @Template("@App/Ndr/Action/start.html.twig")
     */
    public function startAction(int $ndrId): array|RedirectResponse
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getActionsState()['state']) {
            return $this->redirectToRoute('ndr_actions_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/actions/step/{step}", name="ndr_actions_step")
     *
     * @Template("@App/Ndr/Action/step.html.twig")
     */
    public function stepAction(Request $request, int $ndrId, int $step): array|RedirectResponse
    {
        $totalSteps = 4;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_actions_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('ndr_actions', 'ndr_actions_step', 'ndr_actions_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId]);

        $form = $this->createForm(FormDir\Ndr\ActionType::class, $ndr, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->put('ndr/'.$ndrId, $data, ['action']);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'ndr' => $ndr,
            'step' => $step,
            'ndrStatus' => new NdrStatusService($ndr),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/actions/summary", name="ndr_actions_summary")
     *
     * @Template("@App/Ndr/Action/summary.html.twig")
     */
    public function summaryAction(Request $request, $ndrId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getActionsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('ndr_actions', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'ndr' => $ndr,
            'status' => $ndr->getStatusService(),
        ];
    }
}
