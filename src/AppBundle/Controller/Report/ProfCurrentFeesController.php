<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportFeeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\Report\ProfServiceFeeExistType;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-service-fee")
 */
class ProfCurrentFeesController extends AbstractController
{

    private static $jmsGroups = [
        'status',
        'report-prof-service-fees',
        'prof-service-fees',
        'report-prof-estimate-fees'
    ];

    /**
     * @Route("", name="prof_current_fees")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfCurrentFeesState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/exist", name="prof_current_fees_exist")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new ProfServiceFeeExistType(
            $this->get('translator'),
            'report-prof_service_fee'
        ), $report);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var $report EntityDir\Report\Report */
            $report = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $report, ['current-prof-payments-received']);
            if ($report->getCurrentProfPaymentsReceived() == 'no') {
                return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId, 'from'=>'exist']);
            } else {
                return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'from'=>'exist', 'step' => 1]);
            }
        }

        $backLink = $this->generateUrl('prof_current_fees', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('prof_current_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/step/{step}/fee/{feeId}", name="current_service_fee_step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step, $feeId = null)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $totalSteps = 2;
        if ($step == 3 && empty($report->getPreviousProfFeesEstimateGiven())) {
            return $this->redirectToRoute('previous_estimates', ['reportId' => $reportId]);
        }
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId]);
        }

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('prof_current_fees_exist', 'current_service_fee_step', 'previous_estimates', 'prof_current_service_fees_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId'=>$reportId, 'feeId' => $feeId]);

        // create (add mode) or load transaction (edit mode)
        if ($feeId) {
            $profServiceFee = array_filter($report->getCurrentProfServiceFees(), function ($f) use ($feeId) {
                return $f->getId() == $feeId;
            });

            $profServiceFee = array_shift($profServiceFee);
        } else {
            $profServiceFee = new EntityDir\Report\ProfServiceFee();
        }

        if ($step == 3) {
            // crete and handle form
            $form = $this->createForm(new FormDir\Report\ProfServicePreviousFeesEstimateType(
                    $this->get('translator'),
                    'report-prof_service_fee'
                ),
                $report,
                [
                'step' => $step
                ]
            );
        } else {
            // crete and handle form
            $form = $this->createForm(
                new FormDir\Report\ProfServiceFeeType(
                    EntityDir\Report\ProfServiceFee::$serviceTypeIds,
                    $this->get('translator'),
                    'report-prof_service_fee'
                ),
                $profServiceFee,
                [
                    'step' => $step
                ]
            );
        }

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            /* @var $profServiceFee EntityDir\Report\ProfServiceFee */
            $profServiceFee = $form->getData();

            $profServiceFee->setReport($report);

            if ($step == 1) {
                if ($profServiceFee->getId() == null) {
                    $result = $this->getRestClient()->post('report/' . $report->getId() . '/prof-service-fee', $profServiceFee, ['prof-service-fees', 'report-id']);
                } else {
                    $result = $this->getRestClient()->put('prof-service-fee' . $profServiceFee->getId(), $profServiceFee, self::$jmsGroups);
                }

                return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 2, 'feeId' => $result['id']]);
            } elseif ($step == 2) {
                $this->getRestClient()->put('prof-service-fee/' . $profServiceFee->getId(), $profServiceFee, self::$jmsGroups);

                return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 3]);
            } elseif ($step == $totalSteps) {
//                if ($feeId) { // edit
//                    $request->getSession()->getFlashBag()->add(
//                        'notice',
//                        'Entry edited'
//                    );
//
//                    return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId]);
//                } else { // add
//                    $this->getRestClient()->post('/report/' . $reportId . '/professional-fee', $fee, ['prof-service-fees']);
//                    return $this->redirectToRoute('add-another', ['reportId' => $reportId]);
//                }
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'fee' => $profServiceFee,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            //'backLink' => $stepRedirector->getBackLink(),
            //'skipLink' => null,
        ];
    }


    /**
     * @Route("/previous-estimates", name="previous_estimates")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function previousEstimatesAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\Report\ProfServicePreviousFeesEstimateType(
            $this->get('translator'),
            'report-prof_service_fee'
        ), $report);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var $report EntityDir\Report\Report */
            $report = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $report, ['report-prof-estimate-fees']);

            return $this->redirectToRoute(
                'prof_current_service_fees_summary',
                [
                    'reportId' => $reportId,
                    'from'=>'current_service_fee_step'
                ]
            );
        }

//        $backLink = $this->generateUrl('prof_current_fees', ['reportId' => $reportId]);
//        if ($request->get('from') == 'summary') {
//            $backLink = $this->generateUrl('prof_current_fees_summary', ['reportId' => $reportId]);
//        }

        return [
            //'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_current_service_fees_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $reportFeeService = $this->container->get('report_fee_service');

        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $fixedServiceFees = $report->getFilteredFees(
            EntityDir\Report\ProfServiceFee::TYPE_CURRENT_FEE,
            EntityDir\Report\ProfServiceFee::TYPE_FIXED_FEE
        );
        $assessedServiceFees = $report->getFilteredFees(
            EntityDir\Report\ProfServiceFee::TYPE_CURRENT_FEE,
            EntityDir\Report\ProfServiceFee::TYPE_ASSESSED_FEE
        );
        $totalFixedFeesReceived = $reportFeeService->getTotalReceivedFees($fixedServiceFees);
        $totalFixedFeesCharged = $reportFeeService->getTotalChargedFees($fixedServiceFees);
        $totalAssessedFeesReceived = $reportFeeService->getTotalReceivedFees($assessedServiceFees);
        $totalAssessedFeesCharged = $reportFeeService->getTotalChargedFees($assessedServiceFees);

        $grandTotalFeesCharged = number_format(($totalAssessedFeesCharged + $totalFixedFeesCharged), 2, '.', ',');
        $grandTotalFeesReceived = number_format(($totalAssessedFeesReceived + $totalFixedFeesReceived), 2, '.', ',');

        return compact(
            'report',
            'fixedServiceFees',
            'assessedServiceFees',
            'totalFixedFeesCharged',
            'totalFixedFeesReceived',
            'totalAssessedFeesReceived',
            'totalAssessedFeesCharged',
            'grandTotalFeesCharged',
            'grandTotalFeesReceived'
        );
    }

    /**
     * @Route("/delete/fee/{profServiceFeeId}", name="prof_service_fee_delete", requirements={"profServiceFeeId":"\d+"})
     *
     * @param Request $request
     * @param $reportId
     * @param $profServiceFeeId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $profServiceFeeId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->hasProfServiceFeeWithId($profServiceFeeId)) {
            $this->getRestClient()->delete("/prof-service-fee/{$profServiceFeeId}");
            $request->getSession()->getFlashBag()->add('notice', 'Service fee removed');
        }

        return $this->redirect($this->generateUrl('prof_current_service_fees_summary', ['reportId' => $reportId]));
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profCurrentFees';
    }
}
