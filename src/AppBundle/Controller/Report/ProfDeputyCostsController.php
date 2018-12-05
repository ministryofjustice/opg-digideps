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
        'deputy-costs-how-charged',
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
            'backLink' => $fromSummaryPage ? $this->generateUrl('prof_deputy_costs_summary') : null
        ];
    }

    /**
     * @Route("/previous-received-exists", name="prof_deputy_costs_previous_received_exists")
     * @Template()
     */
    public function previousReceivedExists(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\YesNoType::class, $report, [
            'field' => 'profDeputyCostsHasPrevious',
            'translation_domain' => 'report-prof-deputy-costs'
            ]
        );
        $form->handleRequest($request);
        $from = $request->get('from');

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
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // create (add mode) or load transaction (edit mode)
        if ($previousReceivedId) {
            $pr = $this->getRestClient()->get('/report/' . $reportId . '/prof-deputy-previous-cost/' . $previousReceivedId, 'Report\\ProfDeputyPreviousCost');
        } else {
            $pr = new EntityDir\Report\ProfDeputyPreviousCost();
        }

        //TODO in edit mode, only show save and continue to go back to summary
        $form = $this->createForm(FormDir\Report\ProfDeputyCostPreviousType::class, $pr);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {

            if ($previousReceivedId) { // edit
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Entry edited'
                );
                $this->getRestClient()->put('/report/' . $reportId . '/prof-deputy-previous-cost/' . $previousReceivedId, $pr, ['profDeputyPrevCosts']);
            } else { // add
                $this->getRestClient()->post('/report/' . $reportId . '/prof-deputy-previous-cost', $previousReceivedId, ['profDeputyPrevCosts']);
            }

            //TODO buttons goes on different pages
            return $this->redirectToRoute('prof_deputy_costs_summary', ['reportId' => $reportId]);
        }


        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }



    /**
     * @Route("/inline-interim-19b-exists", name="prof_deputy_costs_inline_interim_19b_exists")
     * @Template()
     */
    public function inlineInterim19bExists(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        \Doctrine\Common\Util\Debug::dump($report); die;

        return $this->redirectToRoute('prof_deputy_costs_inline_interim_19b', ['reportId'=>$reportId]);
        return $this->redirectToRoute('prof_deputy_costs_breakdown', ['reportId'=>$reportId]);


        // not for fixed
        //yes / no
    }

    /**
     * @Route("/inline-interim-19b", name="prof_deputy_costs_inline_interim_19b")
     * @Template()
     */
    public function inlineInterim19b(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        \Doctrine\Common\Util\Debug::dump($report); die;

        // 3 x
        // value, date
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

        \Doctrine\Common\Util\Debug::dump($report); die;

        // 7 values + one textarea
        // similar to debts
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
//            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
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
