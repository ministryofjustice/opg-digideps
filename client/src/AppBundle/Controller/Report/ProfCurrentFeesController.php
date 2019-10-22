<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Form\Report\ProfServiceFeeExistType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
        'report-prof-estimate-fees',
    ];

    /**
     * @Route("", name="prof_current_fees")
     * @Template("AppBundle:Report/ProfCurrentFees:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfCurrentFeesState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/exist", name="prof_current_fees_exist")
     * @Template("AppBundle:Report/ProfCurrentFees:exist.html.twig")
     *
     * @param int $reportId
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(ProfServiceFeeExistType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isValid()) {
            switch ($report->getCurrentProfPaymentsReceived()) {
                case 'yes':
                    return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 1, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $report, ['current-prof-payments-received']);

                    return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('prof_current_fees', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form'     => $form->createView(),
            'report'   => $report,
        ];
    }

    /**
     * @Route("/step/{step}/{feeId}", name="current_service_fee_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Report/ProfCurrentFees:step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step, $feeId = null)
    {
        $totalSteps = 2;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
        }
        $fromPage = $request->get('from');

        if ($feeId) { //edit
            $profServiceFee = array_filter($report->getCurrentProfServiceFees(), function ($f) use ($feeId) {
                return $f->getId() == $feeId;
            });
            $profServiceFee = array_shift($profServiceFee);
        } else { // add
            $profServiceFee = new EntityDir\Report\ProfServiceFeeCurrent();
            if (!empty($request->get('serviceTypeId'))) {
                $profServiceFee->setServiceTypeId($request->get('serviceTypeId'));
            }
        }

        // crete and handle form
        $form = $this->createForm(
            FormDir\Report\ProfServiceFeeType::class,
            $profServiceFee, ['step' => $step]
        );

        $form->handleRequest($request);
        $buttonClicked = $form->getClickedButton();

        if ($buttonClicked && $form->isValid()) {
            /* @var $profServiceFee EntityDir\Report\ProfServiceFee */
            $profServiceFee = $form->getData();
            $profServiceFee->setReport($report);

            if ($step == 1) {
                if (!empty($profServiceFee->getId())) {
                    // Update: update service type only
                    $this->getRestClient()->put('prof-service-fee/' . $profServiceFee->getId(), $profServiceFee, ['prof-service-fee-serviceType']);
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been updated');

                    return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 2, 'feeId' => $profServiceFee->getId(), 'from' => $fromPage]);
                }

                // Create. Just redirect to next step
                return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 2, 'serviceTypeId' => $profServiceFee->getServiceTypeId(), 'from' => $fromPage]);
            }

            if ($step == 2) {
                // Check we have a valid service type (now in URL)
                if (!array_key_exists($profServiceFee->getServiceTypeId(), EntityDir\Report\ProfServiceFee::$serviceTypeIds)) {
                    throw new \Exception('Invalid service type');
                }

                if (empty($profServiceFee->getId())) { //NEW
                    // Create: POST entire entity + report
                    $this->getRestClient()->post(
                        'report/' . $report->getId() . '/prof-service-fee',
                        $profServiceFee, ['report-object', 'prof-service-fees']
                    );
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been added');
                } else { // EDIT
                    $this->getRestClient()->put('prof-service-fee/' . $profServiceFee->getId(), $profServiceFee, ['prof-service-fee-serviceType', 'prof-service-fees']);
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been updated');
                }

                // Handle add another pattern
                if ('saveAndAddAnother' === $buttonClicked->getName()) {
                    // use step 1 to begin the loop again
                    return $this->redirectToRoute(
                        'current_service_fee_step', [
                            'reportId' => $reportId,
                            'step'     => 1,
                            'from'     => 'another', //2nd addition will have a link to go back to summary (or breadcrumbs)
                        ]
                    );
                }

                if (empty($report->getPreviousProfFeesEstimateGiven())) {
                    return $this->redirectToRoute('previous_estimates', ['reportId' => $reportId, 'feeId' => $feeId]);
                }

                return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = null;
        if ($step == 1) {
            if ($fromPage =='exist') {
                $backLink =  $this->generateUrl('prof_current_fees_exist', ['reportId' => $reportId]);
            }
            if ($fromPage == 'summary') {
                $backLink =  $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
            }
        }
        if ($step == 2) {
            $backLink =  $this->generateUrl('current_service_fee_step', [
                'reportId' => $reportId,
                'feeId'=>$feeId,
                'from' => $request->get('from'),
                'step'=>1
            ]);
        }


        return [
            'fee'          => $profServiceFee,
            'report'       => $report,
            'step'         => $step,
            'reportStatus' => $report->getStatus(),
            'form'         => $form->createView(),
            'backLink'     => $backLink,
        ];
    }

    /**
     * @Route("/previous-estimates/fee/{feeId}", name="previous_estimates")
     * @Template("AppBundle:Report/ProfCurrentFees:previousEstimates.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function previousEstimatesAction(Request $request, $reportId, $feeId = null)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ProfServicePreviousFeesEstimateType::class, $report);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var $report EntityDir\Report\Report */
            $report = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $report, ['report-prof-estimate-fees']);

            return $this->redirectToRoute(
                'prof_service_fees_summary',
                [
                    'reportId' => $reportId,
                    'from'     => 'current_service_fee_step',
                ]
            );
        }

        $backLink = null;
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form'     => $form->createView(),
            'report'   => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_service_fees_summary")
     * @Template("AppBundle:Report/ProfCurrentFees:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfCurrentFeesState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_current_fees', ['reportId' => $reportId]));
        }

        return
            [
                'report' => $report,
            ];
    }

    /**
     * @Route("/delete/fee/{feeId}", name="prof_service_fee_delete", requirements={"feeId":"\d+"})
     *
     * @param Request $request
     * @param $reportId
     * @param $feeId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $feeId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->hasProfServiceFeeWithId($feeId)) {
            $this->getRestClient()->delete("/prof-service-fee/{$feeId}");
            $request->getSession()->getFlashBag()->add('notice', 'Service fee removed');
        }

        return $this->redirect($this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]));
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profCurrentFees';
    }
}
