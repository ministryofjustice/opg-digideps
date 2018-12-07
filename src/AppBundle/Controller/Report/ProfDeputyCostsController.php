<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs")
 */
class ProfDeputyCostsController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        'prof-deputy-other-costs',
        'prof-deputy-costs-how-charged',
        'report-prof-deputy-costs-prev', // relation
        'prof-deputy-costs-prev', // entity
        'report-prof-deputy-interim', // entity
        'prof-deputy-interim', // entity
    ];

    /**
     * @Route("", name="prof_deputy_costs")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_deputy_costs_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_how_charged")
     * @Template()
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = $request->get('from') == 'summary';

        $form = $this->createForm(FormDir\Report\ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

            $route = $fromSummaryPage ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_previous_received_exists';

            return $this->redirectToRoute($route, ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $fromSummaryPage ? $this->generateUrl('prof_deputy_costs_summary', ['reportId'=>$reportId]) : null
        ];
    }

    /**
     * @Route("/previous-received-exists", name="prof_deputy_costs_previous_received_exists")
     * @Template()
     */
    public function previousReceivedExists(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
            'field' => 'profDeputyCostsHasPrevious',
            'translation_domain' => 'report-prof-deputy-costs'
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getProfDeputyCostsHasPrevious()) {
                case 'yes':
                    // no need to save. "Yes" will be set when one entry is added to keep db data consistent
                    return $this->redirectToRoute('prof_deputy_costs_previous_received', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    // store and go to next route
                    $this->getRestClient()->put('report/' . $reportId, $data, ['profDeputyCostsHasPrevious']);

                    //TODO check with Rob
                    if ($from =='summary') {
                        $nextRoute = 'prof_deputy_costs_summary';
                    } /*else if ($report->profDeputyCostsHowChargedFixed()) {
                        $nextRoute = 'prof_deputy_costs_fixed';
                    } */else {
//                        $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
                        $nextRoute = 'prof_deputy_costs_summary';
                    }

                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}", name="prof_deputy_costs_previous_received")
     * @Template()
     */
    public function previousReceived(Request $request, $reportId, $previousReceivedId = null)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // create (add mode) or load transaction (edit mode)
        if ($previousReceivedId) {
            $pr = $this->getRestClient()->get('/prof-deputy-previous-cost/' . $previousReceivedId, 'Report\\ProfDeputyPreviousCost');
        } else {
            $pr = new EntityDir\Report\ProfDeputyPreviousCost();
        }

        //TODO in edit mode, only show save and continue to go back to summary
        $form = $this->createForm(FormDir\Report\ProfDeputyCostPreviousType::class, $pr, [
            'editMode' =>  !empty($previousReceivedId)
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {

            if ($previousReceivedId) { // edit
                $this->getRestClient()->put('/prof-deputy-previous-cost/' . $previousReceivedId, $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Cost edited'
                );
            } else {
                $this->getRestClient()->post('/report/' . $reportId . '/prof-deputy-previous-cost', $pr, ['profDeputyPrevCosts']);
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Cost added'
                );
            }

            if ($form->getClickedButton()->getName() === 'saveAndAddAnother') {
                $nextRoute = 'prof_deputy_costs_previous_received';
            } else { // saveAndContinue
                $nextRoute = 'prof_deputy_costs_summary'; // TODO use next step
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }


        return [
            'backLink' => $from =='summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/previous-received/{previousReceivedId}/delete", name="prof_deputy_costs_previous_received_delete")
     * @Template()
     *
     * @param Request $request
     * @param $reportId
     * @param $previousReceivedId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function previousCostDelete(Request $request, $reportId, $previousReceivedId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $this->getRestClient()->delete('report/' . $report->getId() . '/prof-deputy-previous-cost/' . $previousReceivedId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Cost deleted'
        );

        return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
    }


    /**
     * @Route("/interim-exists", name="prof_deputy_costs_inline_interim_19b_exists")
     * @Template()
     */
    public function interimExists(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
                'field' => 'profDeputyCostsHasInterim',
                'translation_domain' => 'report-prof-deputy-costs'
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getProfDeputyCostsHasInterim()) {
                case 'yes':
                    // no need to save. "Yes" will be set when one entry is added to keep db data consistent
                    return $this->redirectToRoute('prof_deputy_costs_inline_interim_19b', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    // store and go to next route
                    $this->getRestClient()->put('report/' . $reportId, $data, ['profDeputyCostsHasInterim']);

                    //TODO check with Rob
                    if ($from =='summary') {
                        $nextRoute = 'prof_deputy_costs_summary';
                    } /*else if ($report->profDeputyCostsHowChargedFixed()) {
                        $nextRoute = 'prof_deputy_costs_fixed';
                    } */else {
//                        $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
                        $nextRoute = 'prof_deputy_costs_fixed';
                    }

                    return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
            }
        }

        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/interim", name="prof_deputy_costs_inline_interim_19b")
     * @Template()
     */
    public function interim(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostInterimType::class);

        return [
            'backLink' => $from =='summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/fixed-cost", name="prof_deputy_costs_fixed")
     * @Template()
     */
    public function fixedCost(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        \Doctrine\Common\Util\Debug::dump($report); die;

        // value
    }

    /**
     * @Route("/amount-scco", name="prof_deputy_costs_amount_scco")
     * @Template()
     */
    public function amountToSCCO(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        \Doctrine\Common\Util\Debug::dump($report); die;

        // value
        // textarea
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_breakdown")
     * @Template()
     */
    public function breakdown(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        \Doctrine\Common\Util\Debug::dump($report->getProfDeputyOtherCosts());

        $form = $this->createForm(FormDir\Report\ProfDeputyOtherCostsType::class, $report, [
            ]
        );

        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/summary", name="prof_deputy_costs_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }


    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCosts';
    }
}
