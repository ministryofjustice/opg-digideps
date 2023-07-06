<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Form\Report\ProfServiceFeeExistType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Base route.
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

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("", name="prof_current_fees")
     * @Template("@App/Report/ProfCurrentFees/start.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getProfCurrentFeesState()['state']) {
            return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/exist", name="prof_current_fees_exist")
     * @Template("@App/Report/ProfCurrentFees/exist.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(ProfServiceFeeExistType::class, $report);
        $form->handleRequest($request);
        $fromPage = $request->get('from');

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($report->getCurrentProfPaymentsReceived()) {
                case 'yes':
                    return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'exist']);
                case 'no':
                    $this->restClient->put('report/'.$reportId, $report, ['current-prof-payments-received']);

                    return $this->redirectToRoute('prof_service_fees_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('prof_current_fees', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/step/{step}/{feeId}", name="current_service_fee_step", requirements={"step":"\d+"})
     * @Template("@App/Report/ProfCurrentFees/step.html.twig")
     *
     * @param $reportId
     * @param $step
     * @param null $feeId
     *
     * @return array|RedirectResponse
     *
     * @throws \Exception
     */
    public function stepAction(Request $request, $reportId, $step, $feeId = null)
    {
        $totalSteps = 2;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
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
            $profServiceFee,
            ['step' => $step]
        );

        $form->handleRequest($request);
        $buttonClicked = $form->getClickedButton();

        if ($buttonClicked && $form->isSubmitted() && $form->isValid()) {
            /* @var $profServiceFee EntityDir\Report\ProfServiceFee */
            $profServiceFee = $form->getData();
            $profServiceFee->setReport($report);

            if (1 == $step) {
                if (!empty($profServiceFee->getId())) {
                    // Update: update service type only
                    $this->restClient->put('prof-service-fee/'.$profServiceFee->getId(), $profServiceFee, ['prof-service-fee-serviceType']);
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been updated');

                    return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 2, 'feeId' => $profServiceFee->getId(), 'from' => $fromPage]);
                }

                // Create. Just redirect to next step
                return $this->redirectToRoute('current_service_fee_step', ['reportId' => $reportId, 'step' => 2, 'serviceTypeId' => $profServiceFee->getServiceTypeId(), 'from' => $fromPage]);
            }

            if (2 == $step) {
                // Check we have a valid service type (now in URL)
                if (!array_key_exists($profServiceFee->getServiceTypeId(), EntityDir\Report\ProfServiceFee::$serviceTypeIds)) {
                    throw new \Exception('Invalid service type');
                }

                if (empty($profServiceFee->getId())) { //NEW
                    // Create: POST entire entity + report
                    $this->restClient->post(
                        'report/'.$report->getId().'/prof-service-fee',
                        $profServiceFee,
                        ['report-object', 'prof-service-fees']
                    );
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been added');
                } else { // EDIT
                    $this->restClient->put('prof-service-fee/'.$profServiceFee->getId(), $profServiceFee, ['prof-service-fee-serviceType', 'prof-service-fees']);
//                    $request->getSession()->getFlashBag()->add('notice', 'Service fee has been updated');
                }

                // Handle add another pattern
                if ('saveAndAddAnother' === $buttonClicked->getName()) {
                    // use step 1 to begin the loop again
                    return $this->redirectToRoute(
                        'current_service_fee_step',
                        [
                            'reportId' => $reportId,
                            'step' => 1,
                            'from' => 'another', //2nd addition will have a link to go back to summary (or breadcrumbs)
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
        if (1 == $step) {
            if ('exist' == $fromPage) {
                $backLink = $this->generateUrl('prof_current_fees_exist', ['reportId' => $reportId]);
            }
            if ('summary' == $fromPage) {
                $backLink = $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
            }
        }
        if (2 == $step) {
            $backLink = $this->generateUrl('current_service_fee_step', [
                'reportId' => $reportId,
                'feeId' => $feeId,
                'from' => $request->get('from'),
                'step' => 1,
            ]);
        }

        return [
            'fee' => $profServiceFee,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $backLink,
        ];
    }

    /**
     * @Route("/previous-estimates/fee/{feeId}", name="previous_estimates")
     * @Template("@App/Report/ProfCurrentFees/previousEstimates.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function previousEstimatesAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\ProfServicePreviousFeesEstimateType::class, $report);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $report EntityDir\Report\Report */
            $report = $form->getData();

            $this->restClient->put('report/'.$reportId, $report, ['report-prof-estimate-fees']);

            return $this->redirectToRoute(
                'prof_service_fees_summary',
                [
                    'reportId' => $reportId,
                    'from' => 'current_service_fee_step',
                ]
            );
        }

        $backLink = null;
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('prof_service_fees_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/summary", name="prof_service_fees_summary")
     * @Template("@App/Report/ProfCurrentFees/summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getProfCurrentFeesState()['state']) {
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
     * @param $reportId
     * @param $feeId
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $feeId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->hasProfServiceFeeWithId($feeId)) {
            $this->restClient->delete("/prof-service-fee/{$feeId}");
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
