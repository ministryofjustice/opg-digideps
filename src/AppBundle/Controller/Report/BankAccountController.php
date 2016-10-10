<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BankAccountController extends AbstractController
{
    const STEPS = 1;

    /**
     * @Route("/report/{reportId}/accounts/start", name="bank_accounts")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        if (count($report->getAccounts())) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/step/{step}", name="bank_accounts_step")
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        $comingFromSummaryPage = $request->get('from') === 'summary';

        $account = new EntityDir\Report\Account();
        $account->setReport($report);

        $form = $this->createForm(new FormDir\Report\AccountType($step), $account);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            // if closing balance is set to non-zero values, un-close the account
            if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }

            $this->getRestClient()->post('report/' . $reportId . '/account', $account, ['account']);

            // return to summary if coming from there, or it's the last step
            if ($comingFromSummaryPage) {
                return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId, 'stepEdited' => $step]);
            }
            if ($step == self::STEPS) {
                return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('bank_accounts_step', ['reportId' => $reportId, 'step' => $step + 1]);
        }

        $backLink = null;
        if ($comingFromSummaryPage) {
            $backLink = $this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]);
        } else if ($step == 1) {
            $backLink = $this->generateUrl('bank_accounts', ['reportId' => $reportId]);
        } else { // step > 1
            $backLink = $this->generateUrl('bank_accounts_step', ['reportId' => $reportId, 'step' => $step - 1]);
        }

        return [
            'report' => $report,
            'step' => $step,
            'reportStatus' => new ReportStatusService($report),
            'form' => $form->createView(),
            'backLink' => $backLink,
        ];
    }


    /**
     * @Route("/report/{reportId}/accounts", name="bank_accounts_summary")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        if (count($report->getAccounts()) === 0) {
            return $this->redirectToRoute('bank_accounts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/edit/{id}", name="bank_account_edit", defaults={ "id" = null })
     *
     * @param Request $request
     * @param int $reportId
     * @param int $id account Id
     *
     * @Template()
     *
     * @return array
     */
    public function editAction(Request $request, $reportId, $id)
    {
         $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'client', 'account']);

        if (!$report->hasAccountWithId($id)) {
                throw new \RuntimeException('Account not found.');
            }
        $account = $this->getRestClient()->get('report/account/'.$id, 'Report\\Account');

        $form = $this->createForm(new FormDir\Report\AccountType(), $account);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            // if closing balance is set to non-zero values, un-close the account
            if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }
            $this->getRestClient()->put('/account/'.$id, $account, ['account']);

            return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'account' => $account,
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/{id}/delete", name="account_delete")
     *
     * @param int $reportId
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);

        if ($report->hasAccountWithId($id)) {
            $this->getRestClient()->delete("/account/{$id}");
        }

        return $this->redirect($this->generateUrl('bank_accounts', ['reportId' => $reportId]));
    }
}
