<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\IncomeBenefitsValidator;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IncomeBenefitController extends AbstractController
{
    private static $jmsGroups = [
        'client-cot',
        'odr-income-benefits',
        'odr-income-state-benefits',
        'odr-income-pension',
        'odr-income-damages',
        'odr-income-one-off',
    ];

    /**
     * @Route("/odr/{odrId}/income-benefits", name="odr_income_benefits")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        $oss = new OdrStatusService($odr);
        if ($oss->getIncomeBenefitsState() != OdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('odr_income_benefits_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }


    /**
     * @Route("/odr/{odrId}/income-benefits/step/{step}", name="odr_income_benefits_step")
     * @Template()
     */
    public function stepAction(Request $request, $odrId, $step)
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_income_benefits_summary', ['odrId' => $odrId]);
        }
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutes('odr_income_benefits', 'odr_income_benefits_step', 'odr_income_benefits_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId' => $odrId]);

        $form = $this->createForm(new FormDir\Odr\IncomeBenefitType($step, $this->get('translator'), $odr->getClient()->getFirstname()), $odr);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data Odr */
            $stepToJmsGroup = [
                1 => ['odr-state-benefits'],
                2 => ['odr-receive-state-pension'],
                3 => ['odr-receive-other-income'],
                4 => ['odr-income-damages'],
                5 => ['odr-one-off'],
            ];

            $this->getRestClient()->put('odr/' . $odrId, $data, $stepToJmsGroup[$step]);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }


        return [
            'odr' => $odr,
            'step' => $step,
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/odr/{odrId}/income-benefits/summary", name="odr_income_benefits_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $odrId)
    {
        $fromPage = $request->get('from');
        $odr = $this->getOdr($odrId, self::$jmsGroups);

        // not started -> go back to start page
        $oss = new OdrStatusService($odr);
        if ($oss->getIncomeBenefitsState() == OdrStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step' ) {
            return $this->redirectToRoute('odr_income_benefits', ['odrId' => $odrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'odr' => $odr,
            'validator' => new IncomeBenefitsValidator($odr),
        ];
    }

}
