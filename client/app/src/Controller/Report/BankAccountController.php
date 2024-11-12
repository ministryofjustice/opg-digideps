<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use App\Service\StringUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BankAccountController extends AbstractController
{
    private static $jmsGroups = [
        'account',
        'account-state',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    /** @var StepRedirector */
    private $stepRedirector;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi,
        StepRedirector $stepRedirector,
        private ClientApi $clientApi,
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/report/{reportId}/bank-accounts", name="bank_accounts")
     *
     * @Template("@App/Report/BankAccount/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getBankAccountsState()['state']) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-account/step{step}/{accountId}", name="bank_accounts_step", requirements={"step":"\d+"})
     *
     * @Template("@App/Report/BankAccount/step.html.twig")
     *
     * @param null $accountId
     *
     * @return array|RedirectResponse
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
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('bank_accounts', 'bank_accounts_step', 'bank_accounts_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'accountId' => $accountId]);

        // create (add mode) or load account (edit mode)
        if ($accountId) {
            $account = $this->restClient->get('report/account/'.$accountId, 'Report\\BankAccount');
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
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(FormDir\Report\BankAccountType::class, $account, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // if closing balance is set to non-zero values, un-close the account
            /*if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }*/

            // decide what data in the partial form needs to be passed to next step
            if (1 == $step) {
                $stepUrlData['type'] = $account->getAccountType();
            }

            if (2 == $step) {
                $stepUrlData['bank'] = $account->getBank();
                $stepUrlData['number'] = $account->getAccountNumber();
                $stepUrlData['sort-code'] = $account->getSortCode();
                $stepUrlData['is-joint'] = $account->getIsJointAccount();
            }

            if (3 == $step) {
                $stepUrlData['closing-balance'] = $account->getOpeningBalance();
                $stepUrlData['opening-balance'] = $account->getClosingBalance();
            }

            // 4th step only if closing balance is equals to 0
            $isLastStep = $step == $totalSteps
                || ($step == ($totalSteps - 1) && !$account->isClosingBalanceZero());

            // last step: save
            if ($isLastStep) {
                if ($accountId) {
                    $this->restClient->put('/account/'.$accountId, $account, self::$jmsGroups);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );

                    return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
                } else {
                    $this->restClient->post('report/'.$reportId.'/account', $account, self::$jmsGroups);

                    return $this->redirectToRoute('bank_accounts_add_another', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData,
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
            'gaCustomUrl' => $request->getPathInfo(), // avoid sending query string to GA containing user's data
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-accounts/add_another", name="bank_accounts_add_another")
     *
     * @Template("@App/Report/BankAccount/add_another.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-bank-accounts']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Template("@App/Report/BankAccount/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getBankAccountsState()['state']) {
            return $this->redirectToRoute('bank_accounts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/bank-account/{accountId}/delete", name="bank_account_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteConfirmAction(Request $request, $reportId, $accountId, TranslatorInterface $translator)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $summaryPageUrl = $this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]);

        $dependentRecords = $this->restClient->get("/account/{$accountId}/dependent-records", 'array');
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
        if ($form->isSubmitted() && $form->isValid()) {
            if ($report->getBankAccountById($accountId)) {
                $this->restClient->delete("/account/{$accountId}");
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

        $summary[] = ['label' => 'deletePage.summary.accountNumber', 'value' => '****'.$bankAccount->getAccountNumber()];

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
                if ($count > 0) {
                    $transactionTypes[] = $translator->trans($type, [], 'common');
                }
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
