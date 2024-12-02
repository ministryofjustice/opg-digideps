<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity\Ndr\Ndr;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IncomeBenefitController extends AbstractController
{
    private static $jmsGroups = [
        'state-benefits',
        'pension',
        'damages',
        'one-off',
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
    private $clientApi;

    public function __construct(
        ReportApi $reportApi,
        RestClient $restClient,
        StepRedirector $stepRedirector,
        ClientApi $clientApi
    ) {
        $this->reportApi = $reportApi;
        $this->restClient = $restClient;
        $this->stepRedirector = $stepRedirector;
        $this->clientApi = $clientApi;
    }

    /**
     * @Route("/ndr/{ndrId}/income-benefits", name="ndr_income_benefits")
     *
     * @Template("@App/Ndr/IncomeBenefit/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($ndrId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName(
        ) && $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid());

        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getIncomeBenefitsState()['state']) {
            return $this->redirectToRoute('ndr_income_benefits_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/income-benefits/step/{step}", name="ndr_income_benefits_step")
     *
     * @Template("@App/Ndr/IncomeBenefit/step.html.twig")
     */
    public function stepAction(Request $request, $ndrId, $step, TranslatorInterface $translator)
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_income_benefits_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('ndr_income_benefits', 'ndr_income_benefits_step', 'ndr_income_benefits_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId]);

        $form = $this->createForm(
            FormDir\Ndr\IncomeBenefitType::class,
            $ndr,
            ['step' => $step, 'translator' => $translator, 'clientFirstName' => $ndr->getClient()->getFirstname()]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data Ndr */
            $stepToJmsGroup = [
                1 => ['ndr-state-benefits'],
                2 => ['ndr-receive-state-pension'],
                3 => ['ndr-receive-other-income'],
                4 => ['ndr-income-damages'],
                5 => ['ndr-one-off'],
            ];

            $this->restClient->put('ndr/'.$ndrId, $data, $stepToJmsGroup[$step]);

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
     * @Route("/ndr/{ndrId}/income-benefits/summary", name="ndr_income_benefits_summary")
     *
     * @Template("@App/Ndr/IncomeBenefit/summary.html.twig")
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        // not started -> go back to start page
        if (NdrStatusService::STATE_NOT_STARTED == $ndr->getStatusService()->getIncomeBenefitsState()['state'] && 'skip-step' != $fromPage && 'last-step' != $fromPage) {
            return $this->redirectToRoute('ndr_income_benefits', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'ndr' => $ndr,
            'status' => $ndr->getStatusService(),
        ];
    }
}
