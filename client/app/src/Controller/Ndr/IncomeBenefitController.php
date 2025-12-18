<?php

declare(strict_types=1);

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Form\Ndr\IncomeBenefitType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\NdrStatusService;
use App\Service\StepRedirector;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IncomeBenefitController extends AbstractController
{
    private static array $jmsGroups = [
        'state-benefits',
        'pension',
        'damages',
        'one-off',
    ];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly RestClient $restClient,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    #[Route(path: '/ndr/{ndrId}/income-benefits', name: 'ndr_income_benefits')]
    #[Template('@App/Ndr/IncomeBenefit/start.html.twig')]
    public function startAction(int $ndrId): RedirectResponse|array
    {
        $ndr = $this->reportApi->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if (NdrStatusService::STATE_NOT_STARTED != $ndr->getStatusService()->getIncomeBenefitsState()['state']) {
            return $this->redirectToRoute('ndr_income_benefits_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    #[Route(path: '/ndr/{ndrId}/income-benefits/step/{step}', name: 'ndr_income_benefits_step')]
    #[Template('@App/Ndr/IncomeBenefit/step.html.twig')]
    public function stepAction(Request $request, int $ndrId, $step, TranslatorInterface $translator): RedirectResponse|array
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
            IncomeBenefitType::class,
            $ndr,
            ['step' => $step, 'translator' => $translator, 'clientFirstName' => $ndr->getClient()->getFirstname()]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            /* @var $data Ndr */
            $data = $form->getData();

            $stepToJmsGroup = [
                1 => ['ndr-state-benefits'],
                2 => ['ndr-receive-state-pension'],
                3 => ['ndr-receive-other-income'],
                4 => ['ndr-income-damages'],
                5 => ['ndr-one-off'],
            ];

            $this->restClient->put('ndr/' . $ndrId, $data, $stepToJmsGroup[$step]);

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

    #[Route(path: '/ndr/{ndrId}/income-benefits/summary', name: 'ndr_income_benefits_summary')]
    #[Template('@App/Ndr/IncomeBenefit/summary.html.twig')]
    public function summaryAction(Request $request, int $ndrId): RedirectResponse|array
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
