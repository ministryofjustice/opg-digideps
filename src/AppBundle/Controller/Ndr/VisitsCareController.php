<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    private static $jmsGroups = [
        'visits-care',
    ];

    /**
     * @Route("/ndr/{ndrId}/visits-care", name="ndr_visits_care")
     * @Template()
     */
    public function startAction(Request $request, $ndrId)
    {
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        if ($ndr->getStatusService()->getVisitsCareState()['state'] != NdrStatusService::STATE_NOT_STARTED) {
            return $this->redirectToRoute('ndr_visits_care_summary', ['ndrId' => $ndrId]);
        }

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care/step/{step}", name="ndr_visits_care_step")
     * @Template()
     */
    public function stepAction(Request $request, $ndrId, $step)
    {
        $totalSteps = 5;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('ndr_visits_care_summary', ['ndrId' => $ndrId]);
        }
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
        $visitsCare = $ndr->getVisitsCare() ?: new EntityDir\Ndr\VisitsCare();
        $fromPage = $request->get('from');


        $stepRedirector = $this->stepRedirector()
            ->setRoutes('ndr_visits_care', 'ndr_visits_care_step', 'ndr_visits_care_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['ndrId'=>$ndrId]);

        $form = $this->createForm(FormDir\Ndr\VisitsCareType::class, $visitsCare, [ 'step'            => $step, 'translator'      => $this->get('translator'), 'clientFirstName' => $ndr->getClient()->getFirstname()
                                   ]
                                 );
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Ndr\VisitsCare */
            $data
                ->setNdr($ndr)
                ->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() === null) {
                $this->getRestClient()->post('/ndr/visits-care', $data, ['visits-care', 'ndr-id']);
            } else {
                $this->getRestClient()->put('/ndr/visits-care/' . $visitsCare->getId(), $data, ['visits-care', 'ndr-id']);
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
     * @Template()
     */
    public function summaryAction(Request $request, $ndrId)
    {
        $fromPage = $request->get('from');
        $ndr = $this->getNdrIfNotSubmitted($ndrId, self::$jmsGroups);
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
