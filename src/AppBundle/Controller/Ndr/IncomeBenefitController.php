<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class IncomeBenefitController extends AbstractController
{
    private static $jmsGroups = [
        'state-benefits',
        'pension',
        'damages',
        'one-off',
    ];

    /**
     * @Route("/ndr/{ndrId}/income-benefits", name="ndr_income_benefits")
     * @Template()
     */
    public function startAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getIncomeBenefitsState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_income_benefits_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/income-benefits/step/{step}", name="ndr_income_benefits_step")
     * @Template()
     */
    public function stepAction(Request $request, $ndrId, $step)
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_income_benefits_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('ndr_income_benefits', 'ndr_income_benefits_step', 'ndr_income_benefits_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId' => $ndrId]);

        $form = $this->createForm(FormDir\Ndr\IncomeBenefitType::class, $ndr, [ 'step'            => $step, 'translator'      => $this->get('translator'), 'clientFirstName' => $ndr->getClient()->getFirstname()
                                   ]
                                 );
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data Ndr */
            $stepToJmsGroup = [
                1 => ['ndr-state-benefits'],
                2 => ['ndr-receive-state-pension'],
                3 => ['ndr-receive-other-income'],
                4 => ['ndr-income-damages'],
                5 => ['ndr-one-off'],
            ];

            $this->getRestClient()->put('ndr/' . $ndrId, $data, $stepToJmsGroup[$step]);

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
     * @Route("/ndr/{ndrId}/income-benefits/summary", name="ndr_income_benefits_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);

        // not started -> go back to start page
        if ($ndr->getStatusService()->getIncomeBenefitsState()['state'] == NdrStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step' && $fromPage != 'last-step') {
            return $this->redirectToRoute('ndr_income_benefits', ['ndrId' => $ndrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'ndr' => $ndr,
            'status' => $ndr->getStatusService(),
        ];
    }
}
