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
     * @Route("/report/{reportId}/bank-accounts/start", name="bank_accounts")
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
     * @Route("/report/{reportId}/bank-account/step{step}/{accountId}", name="bank_account_step", requirements={"step":"\d+"})
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step, $accountId = null)
    {
        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $dataToPassToNextStep = $dataFromUrl;
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);
        $comingFromSummaryPage = $request->get('from') === 'summary';
        $defaultRouteParams = [
            'reportId' => $reportId,
            'accountId' => $accountId,
        ];

        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->getRestClient()->get('report/account/' . $accountId, 'Report\\Account');
        } else {
            $account = new EntityDir\Report\Account();
            $account->setReport($report);
        }


        // add URL-data into model
        isset($dataFromUrl['type']) && $account->setAccountType($dataFromUrl['type']);
        isset($dataFromUrl['bank']) && $account->setBank($dataFromUrl['bank']);
        isset($dataFromUrl['number']) && $account->setAccountNumber($dataFromUrl['number']);
        isset($dataFromUrl['sort-code']) && $account->setSortCode($dataFromUrl['sort-code']);
        isset($dataFromUrl['is-joint']) && $account->setIsJointAccount($dataFromUrl['is-joint']);
        isset($dataFromUrl['closing-balance']) && $account->setOpeningBalance($dataFromUrl['closing-balance']);
        isset($dataFromUrl['opening-balance']) && $account->setClosingBalance($dataFromUrl['opening-balance']);

        // crete and handle form
        $form = $this->createForm(new FormDir\Report\AccountType($step), $account);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // if closing balance is set to non-zero values, un-close the account
            /*if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }*/

            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                $dataToPassToNextStep['type'] = $account->getAccountType();
            }

            if ($step == 2) {
                $dataToPassToNextStep['bank'] = $account->getBank();
                $dataToPassToNextStep['number'] = $account->getAccountNumber();
                $dataToPassToNextStep['sort-code'] = $account->getSortCode();
            }

            // last step: save
            if ($step == self::STEPS) {
                if ($accountId) {
                    $this->getRestClient()->put('/account/' . $accountId, $account, ['account']);
                } else {
                    $this->getRestClient()->post('report/' . $reportId . '/account', $account, ['account']);
                }
            }


            // return to summary if coming from there, or it's the last step
            if ($step == self::STEPS || $comingFromSummaryPage) {
                return $this->redirectToRoute('bank_accounts_summary', ['stepEdited' => $step] + $defaultRouteParams);
            }

            return $this->redirectToRoute('bank_account_step', [
                    'step' => $step + 1,
                    'data' => $dataToPassToNextStep
                ] + $defaultRouteParams);
        }

        // generate backlink
        $backLink = null;
        if ($comingFromSummaryPage || $step == self::STEPS) {
            $backLink = $this->generateUrl('bank_accounts_summary', $defaultRouteParams);
        } else if ($step == 1) {
            $backLink = $this->generateUrl('bank_accounts', $defaultRouteParams);
        } else { // step > 1
            // TODO
            $backLink = $this->generateUrl('bank_account_step', ['step' => $step - 1, 'data' => $dataFromUrl] + $defaultRouteParams);
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
     * @Route("/report/{reportId}/bank-accounts", name="bank_accounts_summary")
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
     * @Route("/report/{reportId}/bank-account/{accountId}/delete", name="bank_account_delete")
     *
     * @param int $reportId
     * @param int $accountId
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $accountId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);

        if ($report->hasAccountWithId($accountId)) {
            $this->getRestClient()->delete("/account/{$accountId}");
        }

        return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
    }
}
