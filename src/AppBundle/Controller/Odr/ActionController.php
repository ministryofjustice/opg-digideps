<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\ActionsValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class ActionController extends AbstractController
{
    private static $jmsGroups = [
        'client-cot',
        //TODO merge groups in the API into "odr-action"
        'odr-action-give-gifts',
        'odr-action-property',
        'odr-action-more-info',
    ];


    /**
     * @Route("/odr/{odrId}/actions", name="odr_actions")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if ($odr->hasAtLeastOneAction()) {
            return $this->redirectToRoute('odr_actions_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/actions/step/{step}", name="odr_actions_step")
     * @Template()
     */
    public function stepAction(Request $request, $odrId, $step)
    {
        $totalSteps = 4;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_actions_summary', ['odrId' => $odrId]);
        }
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutes('odr_actions', 'odr_actions_step', 'odr_actions_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId' => $odrId]);

        $form = $this->createForm(new FormDir\Odr\ActionType($step, $this->get('translator'), $odr->getClient()->getFirstname()), $odr);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('odr/' . $odrId , $data, ['action']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'odr'       => $odr,
            'step'         => $step,
            'odrStatus' => new OdrStatusService($odr),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/odr/{odrId}/actions/summary", name="odr_actions_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $odrId)
    {
        $fromPage = $request->get('from');
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        //$this->flagSectionStarted($odr, self::SECTION_ID);
        if (!$odr->hasAtLeastOneAction() && $fromPage != 'skip-step') {
            return $this->redirectToRoute('odr_actions', ['odrId' => $odrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'odr'             => $odr,
            'validator'          => new ActionsValidator($odr),
        ];
    }
}
