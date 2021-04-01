<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class VisitsCareController extends AbstractController
{
    private static $jmsGroups = [
        'visits-care',
    ];

    /**
     * @var ReportApi
     */
    private $reportApi;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var StepRedirector
     */
    private $stepRedirector;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient,
        StepRedirector $stepRedirector
    ) {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care", name="ndr_visits_care")
     * @Template("@App/Ndr/VisitsCare/start.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $ndrId)
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getVisitsCareState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_visits_care_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care/step/{step}", name="ndr_visits_care_step")
     * @Template("@App/Ndr/VisitsCare/step.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     * @param $step
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $ndrId, $step, TranslatorInterface $translator)
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
            ->setRouteBaseParams(['ndrId'=>$ndrId]);

        $form = $this->createForm(
            FormDir\Ndr\VisitsCareType::class,
            $visitsCare,
            [
                'step' => $step,
                'translator' => $translator,
                'clientFirstName' => $ndr->getClient()->getFirstname()
            ]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Ndr\VisitsCare */
            $data
                ->setNdr($ndr)
                ->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() === null) {
                $this->restClient->post('/ndr/visits-care', $data, ['visits-care', 'ndr-id']);
            } else {
                $this->restClient->put('/ndr/visits-care/' . $visitsCare->getId(), $data, ['visits-care', 'ndr-id']);
            }

            if ($fromPage == 'summary') {
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
     * @Template("@App/Ndr/VisitsCare/summary.html.twig")
     *
     * @param Request $request
     * @param $ndrId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getVisitsCareState()['state'] == NdrStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('ndr_visits_care', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'ndr' => $ndr,
            'status'=>$ndr->getStatusService()
        ];
    }
}
