<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\StepRedirector;
use AppBundle\Service\StringUtils;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BankAccountController extends AbstractController
{
    private static $jmsGroups = [
        'account',
        'account-state',
    ];

    /**
     * @Route("/report/{reportId}/bank-accounts", name="bank_accounts")
     * @Template("AppBundle:Report/BankAccount:start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getBankAccountsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-account/step{step}/{accountId}", name="bank_accounts_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Report/BankAccount:step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step, $accountId = null)
    {
        $totalSteps = 4;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('bank_accounts', 'bank_accounts_step', 'bank_accounts_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId'=>$reportId, 'accountId' => $accountId]);

        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->getRestClient()->get('report/account/' . $accountId, 'Report\\BankAccount');
        } else {
            $account = new EntityDir\Report\BankAccount();
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
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // crete and handle form
        $form = $this->createForm(FormDir\Report\BankAccountType::class, $account, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // if closing balance is set to non-zero values, un-close the account
            /*if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }*/

            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                $stepUrlData['type'] = $account->getAccountType();
            }

            if ($step == 2) {
                $stepUrlData['bank'] = $account->getBank();
                $stepUrlData['number'] = $account->getAccountNumber();
                $stepUrlData['sort-code'] = $account->getSortCode();
                $stepUrlData['is-joint'] = $account->getIsJointAccount();
            }

            if ($step == 3) {
                $stepUrlData['closing-balance'] = $account->getOpeningBalance();
                $stepUrlData['opening-balance'] = $account->getClosingBalance();
            }

            // 4th step only if closing balance is equals to 0
            $isLastStep = $step == $totalSteps
                || ($step == ($totalSteps - 1) && !$account->isClosingBalanceZero());

            // last step: save
            if ($isLastStep) {
                if ($accountId) {
                    $this->getRestClient()->put('/account/' . $accountId, $account, self::$jmsGroups);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );

                    return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
                } else {
                    $this->getRestClient()->post('report/' . $reportId . '/account', $account, self::$jmsGroups);

                    return $this->redirectToRoute('bank_accounts_add_another', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'account' => $account,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'gaCustomUrl' => $request->getPathInfo() // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-accounts/add_another", name="bank_accounts_add_another")
     * @Template("AppBundle:Report/BankAccount:add_another.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-bank-accounts']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('bank_accounts_step', ['reportId' => $reportId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-accounts/summary", name="bank_accounts_summary")
     *
     * @param int $reportId
     * @Template("AppBundle:Report/BankAccount:summary.html.twig")
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getBankAccountsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('bank_accounts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-account/{accountId}/delete", name="bank_account_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $reportId
     * @param int $accountId
     */
    public function deleteConfirmAction(Request $request, $reportId, $accountId)
    {
        $translator = $this->get('translator');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $summaryPageUrl = $this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]);

        $dependentRecords = $this->getRestClient()->get("/account/{$accountId}/dependent-records", 'array');
        $bankAccount = $report->getBankAccountById($accountId);

        // if money transfer are added, always go to summary page with the error displayed
        if ($dependentRecords['moneyTransfers'] > 0) {
            $translatedMessage = $translator->trans('deletePage.transferPresentError', [], 'report-bank-accounts');
            $request->getSession()->getFlashBag()->add('error', $translatedMessage);

            return $this->redirect($summaryPageUrl);
        }

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        // delete the bank acount if the confirm button is pushed, or there are no payments. Then go back to summary page
        if ($form->isValid()) {
            if ($report->getBankAccountById($accountId)) {
                $this->getRestClient()->delete("/account/{$accountId}");
            }

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Bank account deleted'
            );

            return $this->redirect($summaryPageUrl);
        }

        // Build summary based on information entered
        $summary = [];

        if ($bankAccount->requiresBankName()) {
            $summary[] = ['label' => 'deletePage.summary.bank', 'value' => $bankAccount->getBank()];
        }

        $summary[] = ['label' => 'deletePage.summary.accountType', 'value' => $bankAccount->getAccountTypeText()];

        if ($bankAccount->requiresSortCode()) {
            $summary[] = ['label' => 'deletePage.summary.sortCode', 'value' => $bankAccount->getDisplaySortCode()];
        }

        $summary[] = ['label' => 'deletePage.summary.accountNumber', 'value' => '****' . $bankAccount->getAccountNumber()];

        // show confirmation page
        $templateData = [
            'translationDomain' => 'report-bank-accounts',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $summaryPageUrl,
        ];

        // Show a warning if the account has transactions
        if ($dependentRecords['transactionsCount'] > 0) {
            $transactionTypes = [];
            foreach ($dependentRecords['transactions'] as $type => $count) {
                if ($count > 0) $transactionTypes[] = $translator->trans($type, [], 'common');
            }

            $templateData['warning'] = $translator->trans(
                'deletePage.linkedPaymentsWarning',
                ['%paymentTypes%' => StringUtils::implodeWithDifferentLast($transactionTypes, ', ', ' and ')],
                'report-bank-accounts'
            );
        }

        return $templateData;
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'bankAccounts';
    }
}
