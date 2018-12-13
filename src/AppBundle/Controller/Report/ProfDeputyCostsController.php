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
        'report-prof-deputy-costs-prev', 'prof-deputy-costs-prev',
        'report-prof-deputy-costs-interim', 'prof-deputy-costs-interim',
        'report-prof-deputy-costs-scco'
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
        $from = $request->get('from');

        $form = $this->createForm(FormDir\Report\ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

            if ($from === 'summary') {
                $nextRoute = 'prof_deputy_costs_summary';
            } else {
                $nextRoute = 'prof_deputy_costs_previous_received_exists';
            }

            return $this->redirectToRoute($nextRoute, ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $from === 'summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId'=>$reportId]) : null
        ];
    }

    /**
     * @Route("/previous-received-exists", name="prof_deputy_costs_previous_received_exists")
     * @Template()
     */
    public function previousReceivedExists(Request $request, $reportId)
    {
        $from = $request->get('from', 'exist');
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
                    return $this->redirectToRoute('prof_deputy_costs_previous_received', ['reportId' => $reportId, 'from'=>$from]);
                case 'no':
                    // store and go to next route
                    $this->getRestClient()->put('report/' . $reportId, $data, ['profDeputyCostsHasPrevious']);

                    if ($from =='summary') {
                        $nextRoute = 'prof_deputy_costs_summary';
                    } else if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                        $nextRoute = 'prof_deputy_costs_fixed';
                    } else {
                        $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
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
            } else if ($from === 'summary') {
                $nextRoute = 'prof_deputy_costs_summary';
            } else if ($report->hasProfDeputyCostsHowChargedFixedOnly()) {
                $nextRoute = 'prof_deputy_costs_fixed';
            } else {
                $nextRoute = 'prof_deputy_costs_inline_interim_19b_exists';
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
        $from = $request->get('from', 'exist');
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
                    return $this->redirectToRoute('prof_deputy_costs_inline_interim_19b', ['reportId' => $reportId, 'from'=>$from]);
                case 'no':
                    // store and go to next route
                    $this->getRestClient()->put('report/' . $reportId, $data, ['profDeputyCostsHasInterim']);

                    if ($from === 'summary') {
                        $nextRoute = 'prof_deputy_costs_summary';
                    } else {
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
        // fill missing interim with empty entities, in order for 3 subforms in total to appear
        for($i = count($report->getProfDeputyInterimCosts()); $i < 3; $i++) {
            $report->addProfDeputyInterimCosts(new EntityDir\Report\ProfDeputyInterimCost());
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyCostInterimType::class, $report);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getRestClient()->put('/report/' . $reportId, $report, ['profDeputyInterimCosts']);

            if ($from === 'summary') {
                $nextRoute = 'prof_deputy_costs_summary';
            } else { // saveAndContinue
                $nextRoute = 'prof_deputy_costs_amount_scco';
            }

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $this->generateUrl($from =='summary' ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_inline_interim_19b_exists', ['reportId' => $reportId]),
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
    public function AmountToSccoAction(Request $request, $reportId)
    {
        $from = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostSccoType::class, $report);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getRestClient()->put('/report/' . $reportId, $report, ['profDeputyCostsScco']);

            $nextRoute = ($from === 'summary') ? 'prof_deputy_costs_summary' : 'prof_deputy_costs_breakdown';

            return $this->redirectToRoute($nextRoute, ['reportId' => $reportId]);
        }

        return [
            'backLink' => $from =='summary' ? $this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]) : null,
            'form' => $form->createView(),
            'report' => $report
        ];
    }

    /**
     * @Route("/breakdown", name="prof_deputy_costs_breakdown")
     * @Template()
     */
    public function breakdown(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (empty($report->getProfDeputyOtherCosts())) {
            // if none set generate other costs manually
            $otherCosts = $this->generateDefaultOtherCosts($report);

            $report->setProfDeputyOtherCosts($otherCosts);
        }

        $form = $this->createForm(FormDir\Report\ProfDeputyOtherCostsType::class, $report, []);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/' . $report->getId(), $form->getData(), ['prof-deputy-other-costs']);

            return $this->redirect($this->generateUrl('prof_deputy_costs_summary', ['reportId' => $reportId]));
        }

        return [
            'backLink' => null,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * Retrieves the list of default other cost type IDs using virtual property from api
     * Used to generate the page since with no initial data, we cant display form inputs
     * without this list.
     *
     * @param EntityDir\Report\Report $report
     * @return array
     */
    private function generateDefaultOtherCosts(EntityDir\Report\Report $report)
    {
        $otherCosts = [];

        $defaultOtherCostTypeIds = $report->getProfDeputyOtherCostTypeIds();
        foreach ($defaultOtherCostTypeIds as $defaultOtherCostType) {
            $otherCosts[] = new EntityDir\Report\ProfDeputyOtherCost(
                $defaultOtherCostType['typeId'],
                null,
                $defaultOtherCostType['hasMoreDetails'],
                null
            );

        }
        return $otherCosts;
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
