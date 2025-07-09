<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class VisitsCareController extends AbstractController
{
    private static $jmsGroups = [
        'visits-care',
    ];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly RestClient $restClient,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care", name="ndr_visits_care")
     *
     * @Template("@App/Ndr/VisitsCare/start.html.twig")
     */
    public function startAction(Request $request, int $ndrId): array|RedirectResponse
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getVisitsCareState()['state']) {
            return $this->redirectToRoute('ndr_visits_care_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care/step/{step}", name="ndr_visits_care_step")
     *
     * @Template("@App/Ndr/VisitsCare/step.html.twig")
     */
    public function stepAction(Request $request, int $ndrId, int $step, TranslatorInterface $translator): array|RedirectResponse
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_visits_care_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $visitsCare = $ndr->getVisitsCare() ?: new EntityDir\Ndr\VisitsCare();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('ndr_visits_care', 'ndr_visits_care_step', 'ndr_visits_care_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId]);

        $form = $this->createForm(
            FormDir\Ndr\VisitsCareType::class,
            $visitsCare,
            [
                'step' => $step,
                'translator' => $translator,
                'clientFirstName' => $ndr->getClient()->getFirstname(),
            ]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            /* @var EntityDir\Ndr\VisitsCare $data */
            $data = $form->getData();

            $data->setNdr($ndr)
                ->keepOnlyRelevantVisitsCareData();

            if (null === $visitsCare->getId()) {
                $this->restClient->post('/ndr/visits-care', $data, ['visits-care', 'ndr-id']);
            } else {
                $this->restClient->put('/ndr/visits-care/'.$visitsCare->getId(), $data, ['visits-care', 'ndr-id']);
            }

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
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care/summary", name="ndr_visits_care_summary")
     *
     * @Template("@App/Ndr/VisitsCare/summary.html.twig")
     */
    public function summaryAction(Request $request, int $ndrId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getVisitsCareState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('ndr_visits_care', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'ndr' => $ndr,
            'status' => $ndr->getStatusService(),
        ];
    }
}
