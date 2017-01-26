<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\VisitsCareValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    private static $jmsGroups = [
        'visits-care',
    ];


    /**
     * @Route("/odr/{odrId}/visits-care", name="odr_visits_care")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        if ($odr->getVisitsCare() != null/* || $odr->isSectionStarted(self::SECTION_ID)*/) {
            return $this->redirectToRoute('odr_visits_care_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }


    /**
     * @Route("/odr/{odrId}/visits-care/step/{step}", name="odr_visits_care_step")
     * @Template()
     */
    public function stepAction(Request $request, $odrId, $step)
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_visits_care_summary', ['odrId' => $odrId]);
        }
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        $visitsCare = $odr->getVisitsCare() ?: new EntityDir\Odr\VisitsCare();
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('odr_visits_care', 'odr_visits_care_step', 'odr_visits_care_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId'=>$odrId]);

        $form = $this->createForm(new FormDir\Odr\VisitsCareType($step, $this->get('translator'), $odr->getClient()->getFirstname()), $visitsCare);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Odr\VisitsCare */
            $data
                ->setOdr($odr)
                ->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() === null) {
                $this->getRestClient()->post('/odr/visits-care', $data, ['visits-care', 'odr-id']);
            } else {
                $this->getRestClient()->put('/odr/visits-care/'.$visitsCare->getId(), $data, ['visits-care', 'odr-id']);
            }

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Record edited'
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
     * @Route("/odr/{odrId}/visits-care/summary", name="odr_visits_care_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $odrId)
    {
        $fromPage = $request->get('from');
        $odr = $this->getOdrIfNotSubmitted($odrId, self::$jmsGroups);
        //$this->flagSectionStarted($odr, self::SECTION_ID);
        if (!$odr->getVisitsCare() && $fromPage != 'skip-step') {
            return $this->redirectToRoute('odr_visits_care', ['odrId' => $odrId]);
        }

        if (!$odr->getVisitsCare()) { //allow validation with answers all skipped
            $odr->setVisitsCare(new EntityDir\Odr\VisitsCare());
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'odr' => $odr,
            'validator' => new VisitsCareValidator($odr->getVisitsCare()),
        ];
    }
}
