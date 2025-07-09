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

class OtherInfoController extends AbstractController
{
    private static $jmsGroups = [
        'ndr-action-more-info',
    ];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly RestClient $restClient,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    /**
     * @Route("/ndr/{ndrId}/any-other-info", name="ndr_other_info")
     *
     * @Template("@App/Ndr/OtherInfo/start.html.twig")
     */
    public function startAction(Request $request, int $ndrId): array|RedirectResponse
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getOtherInfoState()['state']) {
            return $this->redirectToRoute('ndr_other_info_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/any-other-info/step/{step}", name="ndr_other_info_step")
     *
     * @Template("@App/Ndr/OtherInfo/step.html.twig")
     */
    public function stepAction(Request $request, int $ndrId, int $step): array|RedirectResponse
    {
        $totalSteps = 1; // only one step but convenient to reuse the "step" logic and keep things aligned/simple
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_other_info_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('ndr_other_info', 'ndr_other_info_step', 'ndr_other_info_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId]);

        $form = $this->createForm(FormDir\Ndr\OtherInfoType::class, $ndr);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->put('ndr/'.$ndrId, $data, ['more-info']);

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
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/any-other-info/summary", name="ndr_other_info_summary")
     *
     * @Template("@App/Ndr/OtherInfo/summary.html.twig")
     */
    public function summaryAction(Request $request, int $ndrId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getOtherInfoState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('ndr_other_info', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'ndr' => $ndr,
        ];
    }
}
