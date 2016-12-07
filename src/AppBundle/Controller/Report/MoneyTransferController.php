<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MoneyTransferController extends AbstractController
{
    const STEPS = 2;

    /**
     * @Route("/report/{reportId}/money-transfers", name="money_transfers")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportByIdWithChecks($reportId);
        if (count($report->getAccounts()) < 2) {
            return $this->render('AppBundle:Report/MoneyTransfer:error.html.twig', [
                'error' => 'atLeastTwoBankAccounts',
                'report' => $report,
            ]);
        }

        if (count($report->getMoneyTransfers()) > 0 || $report->getNoTransfersToAdd()) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/exist", name="money_transfers_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportByIdWithChecks($reportId);
        $form = $this->createForm(new FormDir\Report\MoneyTransferExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($report->getNoTransfersToAdd()) {
                case false:
                    return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'step' => 1]);
                case true:
                    $this->get('restClient')->put('report/' . $reportId, $report, ['money-transfers-no-transfers']);
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('money_transfers', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/step{step}/{transferId}", name="money_transfers_step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step, $transferId = null)
    {
        if ($step < 1 || $step > self::STEPS) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportByIdWithChecks($reportId);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutePrefix('money_transfers_')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps(self::STEPS)
            ->setRouteBaseParams(['reportId' => $reportId, 'transferId' => $transferId]);


        // create (add mode) or load transaction (edit mode)
        if ($transferId) {
            $transfer = $report->getMoneyTransferWithId($transferId);
            $transfer->setAccountFromId($transfer->getAccountFrom()->getId());
            $transfer->setAccountToId($transfer->getAccountTo()->getId());
        } else {
            $transfer = new EntityDir\Report\MoneyTransfer();
        }

        // add URL-data into model
        if (isset($dataFromUrl['from-id']) && isset($dataFromUrl['to-id'])) {
            $transfer->setAccountFromId($dataFromUrl['from-id']);
            $transfer->setAccountFrom($report->getAccountWithId($dataFromUrl['from-id']));
            $transfer->setAccountToId($dataFromUrl['to-id']);
            $transfer->setAccountTo($report->getAccountWithId($dataFromUrl['to-id']));
        }
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // crete and handle form
        $form = $this->createForm(new FormDir\Report\MoneyTransferType(
            $step, $report->getAccounts()
        ), $transfer);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                $stepUrlData['from-id'] = $transfer->getAccountFromId();
                $stepUrlData['to-id'] = $transfer->getAccountToId();
            } else if ($step == self::STEPS) {
                if ($transferId) { // edit
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Entry edited'
                    );
                    $this->getRestClient()->put('/report/' . $reportId . '/money-transfers/' . $transferId, $transfer, ['money-transfer']);

                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
                } else { // add
                    $this->getRestClient()->post('/report/' . $reportId . '/money-transfers', $transfer, ['money-transfer']);
                    return $this->redirectToRoute('money_transfers_add_another', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'transfer' => $transfer,
            'report' => $report,
            'step' => $step,
            'reportStatus' => new ReportStatusService($report),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/add_another", name="money_transfers_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportByIdWithChecks($reportId);

        $form = $this->createForm(new FormDir\Report\MoneyTransferAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'from' => 'another', 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/summary", name="money_transfers_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportByIdWithChecks($reportId);
        if (count($report->getMoneyTransfers()) == 0 && $report->getNoTransfersToAdd() === null) {
            return $this->redirect($this->generateUrl('money_transfers', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}/delete", name="money_transfers_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transferId)
    {
        $this->getRestClient()->delete("/report/{$reportId}/money-transfers/{$transferId}");

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Money transfer deleted'
        );

        return $this->redirect($this->generateUrl('money_transfers_summary', ['reportId' => $reportId]));
    }

    private function getReportByIdWithChecks($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['money-transfer', 'account']);


        return $report;
    }

}
