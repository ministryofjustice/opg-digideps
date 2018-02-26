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
 * @Route("/report/{reportId}/prof-current-fees")
 */
class ProfCurrentFeesController extends AbstractController
{
    private static $jmsGroups = [
        'status'
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

        $form = $this->createForm(FormDir\Report\ProfServiceFeeExistType::class, $report);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var $report EntityDir\Report\Report */
            $report = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $report, ['current-prof-payments-received']);
            if ($report->getCurrentProfPaymentsReceived() == 'no') {
                return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId, 'from'=>'exist']);
            } else {
                return $this->redirectToRoute('current-service-fee-step', ['reportId' => $reportId, 'from'=>'exist', 'step' => 1]);
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
     * @Route("/step/{step}/fee/{feeId}", name="current-service-fee-step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step, $feeId = null)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('prof_current_fees_exist', 'current-service-fee-step', 'prof_current_service_fees_summary')
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

        // add URL-data into model
        //isset($dataFromUrl['group']) && $fee->setGroup($dataFromUrl['group']);
        //isset($dataFromUrl['category']) && $fee->setCategory($dataFromUrl['category']);
//        $stepRedirector->setStepUrlAdditionalParams([
//            'data' => $dataFromUrl
//        ]);

        // crete and handle form
        $form = $this->createForm(
            FormDir\Report\ProfServiceFeeType::class,
            $profServiceFee,
            [
                'step' => $step,
                //'feeTypeId' => 'current',
                //'translator' => $this->get('translator'),
                //'clientFirstName' => $report->getClient()->getFirstname(),
                //'selectedGroup' => $fee->getGroup(),
                //'selectedCategory' => $fee->getCategory()
            ]
        );

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $profServiceFee = $form->getData();
            /* @var $profServiceFee EntityDir\Report\ProfServiceFee */
            $profServiceFee->setReport($report);

            if ($step == 1) {
                if ($profServiceFee->getId() == null) {
                    $this->getRestClient()->post('report/' . $report->getId() . '/prof-service-fee', $profServiceFee, ['prof-service-fees', 'report-id']);
                } else {
                    $this->getRestClient()->put('report/' . $report->getId() . '/prof-service-fee' . $profServiceFee->getId(), $profServiceFee, self::$jmsGroups);
                }

                return $this->redirectToRoute('current-service-fee-step', ['reportId' => $reportId, 'step' => 2]);
            } elseif ($step == 2) {
                // todo PUT profFee
                return $this->redirectToRoute('current-service-fee-step', ['reportId' => $reportId, 'step' => 3]);
            } elseif ($step == $totalSteps) {
//                if ($feeId) { // edit
//                    $request->getSession()->getFlashBag()->add(
//                        'notice',
//                        'Entry edited'
//                    );
//                    $this->getRestClient()->put('/report/' . $reportId . '/professional-fee/' . $feeId, $fee, ['prof-service-fees']);
                    return $this->redirectToRoute('prof_current_service_fees_summary', ['reportId' => $reportId]);
//                } else { // add
//                    $this->getRestClient()->post('/report/' . $reportId . '/professional-fee', $fee, ['prof-service-fees']);
//                    return $this->redirectToRoute('add-another', ['reportId' => $reportId]);
//                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

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
     * @Route("/summary", name="prof_current_service_fees_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {

        return [
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profCurrentFees';
    }
}
