<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Status;
use App\Form\AddAnotherThingType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\BankAccountType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use App\Service\StringUtils;
use OPG\Digideps\Common\Validating\ValidatingForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BankAccountController extends AbstractController
{
    private static array $jmsGroups = [
        'account',
        'account-state',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    #[Route(path: '/report/{reportId}/bank-accounts', name: 'bank_accounts')]
    #[Template('@App/Report/BankAccount/start.html.twig')]
    public function startAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = $report->getStatus()->getBankAccountsState();
        if (Status::STATE_NOT_STARTED != $status['state']) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/bank-account/step{step}/{accountId}', name: 'bank_accounts_step', requirements: ['step' => '\d+'])]
    #[Template('@App/Report/BankAccount/step.html.twig')]
    public function stepAction(Request $request, int $reportId, int $step, ?int $accountId = null): array|RedirectResponse
    {
        $totalSteps = 3;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        /** @var array $dataFromRequest */
        $dataFromRequest = $request->get('data') ?: [];

        $stepUrlData = $dataFromRequest;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $fromPage = $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

        $stepRedirector = $this->stepRedirector
            ->setRoutes('bank_accounts', 'bank_accounts_step', 'bank_accounts_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'accountId' => $accountId]);

        // create (add mode) or load (edit mode) account
        if (is_null($accountId)) {
            $account = new BankAccount();
            $account->setReport($report);
        } else {
            /** @var BankAccount $account */
            $account = $this->restClient->get('report/account/' . $accountId, 'Report\\BankAccount');
        }

        // add URL-data into model
        isset($dataFromRequest['type']) && $account->setAccountType($dataFromRequest['type']);
        isset($dataFromRequest['bank']) && $account->setBank($dataFromRequest['bank']);
        isset($dataFromRequest['number']) && $account->setAccountNumber($dataFromRequest['number']);
        isset($dataFromRequest['sort-code']) && $account->setSortCode($dataFromRequest['sort-code']);
        isset($dataFromRequest['is-joint']) && $account->setIsJointAccount($dataFromRequest['is-joint']);
        isset($dataFromRequest['closing-balance']) && $account->setOpeningBalance($dataFromRequest['closing-balance']);
        isset($dataFromRequest['opening-balance']) && $account->setClosingBalance($dataFromRequest['opening-balance']);
        isset($dataFromRequest['is-closed']) && $account->setIsClosed($dataFromRequest['is-closed']);
        $stepRedirector->setStepUrlAdditionalParams(['data' => $dataFromRequest]);

        // create and handle form
        $form = $this->createForm(BankAccountType::class, $account, ['step' => $step]);

        // if we are in add mode and on the last step, show radio buttons to give the option to add another account
        if ($step === $totalSteps && empty($accountId)) {
            $form->add('addAnother', AddAnotherThingType::class);
        }

        $form->handleRequest($request);

        /** @var SubmitButton $submitBtn */
        $submitBtn = $form->get('save');
        if ($submitBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if (1 === $step) {
                $stepUrlData['type'] = $account->getAccountType();
            }

            if (2 === $step) {
                $stepUrlData['bank'] = $account->getBank();
                $stepUrlData['number'] = $account->getAccountNumber();
                $stepUrlData['sort-code'] = $account->getSortCode();
                $stepUrlData['is-joint'] = $account->getIsJointAccount();
            }

            // redirect to next step if not on the last step
            if ($step !== $totalSteps) {
                $stepRedirector->setStepUrlAdditionalParams(['data' => $stepUrlData]);
                return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
            }

            // we're on the last step
            if ($accountId) {
                // replace existing account
                $this->restClient->put('/account/' . $accountId, $account, self::$jmsGroups);
                if ($request->getSession() instanceof Session) {
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Bank account edited'
                    );
                }

                return $this->redirect($this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]));
            }

            // create new account
            $this->restClient->post('report/' . $reportId . '/account', $account, self::$jmsGroups);

            // redirect to add another if requested
            $validatedForm = new ValidatingForm($form);
            $addAnother = $validatedForm->getStringOrNull('addAnother');
            if ('yes' === $addAnother) {
                return $this->redirectToRoute('bank_accounts_step', ['reportId' => $reportId, 'step' => 1]);
            }

            return $this->redirectToRoute('bank_accounts_summary', ['reportId' => $reportId]);
        }

        return [
            'account' => $account,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
        ];
    }

    #[Route(path: '/report/{reportId}/bank-accounts/summary', name: 'bank_accounts_summary')]
    #[Template('@App/Report/BankAccount/summary.html.twig')]
    public function summaryAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = $report->getStatus()->getBankAccountsState();
        if (Status::STATE_NOT_STARTED == $status['state']) {
            return $this->redirectToRoute('bank_accounts', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/bank-account/{accountId}/delete', name: 'bank_account_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteConfirmAction(
        Request $request,
        int $reportId,
        int $accountId,
        TranslatorInterface $translator
    ): array|RedirectResponse {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $summaryPageUrl = $this->generateUrl('bank_accounts_summary', ['reportId' => $reportId]);

        /** @var array $dependentRecords */
        $dependentRecords = $this->restClient->get("/account/$accountId/dependent-records", 'array');
        $bankAccount = $report->getBankAccountById($accountId);

        // if money transfer are added, always go to summary page with the error displayed
        if ($dependentRecords['moneyTransfers'] > 0) {
            $translatedMessage = $translator->trans('deletePage.transferPresentError', [], 'report-bank-accounts');

            if ($request->getSession() instanceof Session) {
                $request->getSession()->getFlashBag()->add('error', $translatedMessage);
            }

            return $this->redirect($summaryPageUrl);
        }

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        // delete the bank account if the confirm button is pushed, or there are no payments. Then go back to summary page
        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/account/$accountId");

            if ($request->getSession() instanceof Session) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Bank account deleted'
                );
            }

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
}
