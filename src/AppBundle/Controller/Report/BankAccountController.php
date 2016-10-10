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
    const STEPS = 3;

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
        $dataFromUrl = $request->get('data') ?: [];
        $dataToPassToNextStep = $dataFromUrl;

        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        $comingFromSummaryPage = $request->get('from') === 'summary';

        $account = new EntityDir\Report\Account();
        $account->setReport($report);
        // set data from URL
        $account->setAccountType($dataFromUrl['type']);
        $account->setBank($dataFromUrl['bank']);
        $account->setAccountNumber($dataFromUrl['number']);
        $account->setSortCode($dataFromUrl['sort-code']);
        $account->setIsJointAccount($dataFromUrl['is-joint']);
        $account->setOpeningBalance($dataFromUrl['closing-balance']);
        $account->setClosingBalance($dataFromUrl['opening-balance']);

        $form = $this->createForm(new FormDir\Report\AccountType($step), $account);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
//            $data = $form->getData();
            // if closing balance is set to non-zero values, un-close the account
            /*if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }*/

            if ($step == 1) {
                $dataToPassToNextStep['type'] = $account->getAccountType();
            }

            if ($step == 2) {
                $dataToPassToNextStep['bank'] = $account->getBank();
                $dataToPassToNextStep['number'] = $account->getAccountNumber();
                $dataToPassToNextStep['sort-code'] = $account->getSortCode();
            }

            if ($step == 3) {
                $dataToPassToNextStep['is-joint'] = $account->getIsJointAccount();
                $dataToPassToNextStep['closing-balance'] = $account->getOpeningBalance();
                $dataToPassToNextStep['opening-balance'] = $account->getClosingBalance();
                //TODO
                // think about generic function to save/load from string into the model
            }

            // return to summary if coming from there, or it's the last step
            if ($comingFromSummaryPage) {
                return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId, 'stepEdited' => $step]);
            }
            if ($step == self::STEPS) {
                echo "<pre>";
                \Doctrine\Common\Util\Debug::dump('save time !', 4);
                die;
                $this->getRestClient()->post('report/' . $reportId . '/account', $account, ['account']);
                return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
            }

            return $this->redirectToRoute('bank_accounts_step', [
                'account' => $account,
                'reportId' => $reportId,
                'step' => $step + 1,
                'data' => $dataToPassToNextStep
            ]);
        }

        $backLink = null;
        if ($comingFromSummaryPage) {
            $backLink = $this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]);
        } else if ($step == 1) {
            $backLink = $this->generateUrl('bank_accounts', ['reportId' => $reportId]);
        } else { // step > 1
            // TODO
            $backLink = $this->generateUrl('bank_accounts_step', ['reportId' => $reportId, 'step' => $step - 1, 'data' => $dataFromUrl]);
        }


        return [
            'account' => $account,
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
        $account = $this->getRestClient()->get('report/account/' . $id, 'Report\\Account');

        $form = $this->createForm(new FormDir\Report\AccountType(), $account);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            // if closing balance is set to non-zero values, un-close the account
            if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }
            $this->getRestClient()->put('/account/' . $id, $account, ['account']);

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
