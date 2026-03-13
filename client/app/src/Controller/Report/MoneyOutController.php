<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\Report;
use App\Entity\Report\Status;
use App\Form\AddAnotherThingType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\DoesMoneyOutExistType;
use App\Form\Report\MoneyTransactionType;
use App\Form\Report\NoMoneyOutType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use App\Utility\ValidatingForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MoneyOutController extends AbstractController
{
    private const int TOTAL_STEPS = 2;

    private static array $jmsGroups = [
        'transactionsOut',
        'money-out-state',
        'account',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/report/{reportId}/money-out', name: 'money_out')]
    #[Template('@App/Report/MoneyOut/start.html.twig')]
    public function startAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyOutState()['state']) {
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/money-out/exist', name: 'does_money_out_exist')]
    #[Template('@App/Report/MoneyOut/exist.html.twig')]
    public function existAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(DoesMoneyOutExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $report = $validatingForm->getObjectOrThrow(null, Report::class);
            $answer = $validatingForm->getStringOrNull('moneyOutExists');
            $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

            $report->setMoneyOutExists($answer);
            $this->restClient->put('report/' . $reportId, $report, ['doesMoneyOutExist']);

            // retrieve soft deleted transaction ids if present and handle money out ids only
            $softDeletedTransactionIds = $this->restClient->get('/report/' . $reportId . '/money-transaction/get-soft-delete', 'Report\MoneyTransaction[]');

            $softDeletedMoneyOutTransactionIds = [];
            foreach ($softDeletedTransactionIds as $softDeletedTransactionId) {
                if ('out' == $softDeletedTransactionId->getType()) {
                    $softDeletedMoneyOutTransactionIds[] = $softDeletedTransactionId->getId();
                }
            }

            if ('Yes' === $answer && 'summary' !== $fromPage) {
                $report->setReasonForNoMoneyOut(null);
                $this->restClient->put("report/$reportId", $report, ['reasonForNoMoneyOut']);

                return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_out_exist']);
            } elseif ('Yes' === $answer && 'summary' === $fromPage) {
                $report->setReasonForNoMoneyOut(null);
                $this->restClient->put("report/$reportId", $report, ['reasonForNoMoneyOut']);

                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedMoneyOutTransactionIds, $report);

                $moneyOutStepRedirectParameters = ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_out_exist'];
                $moneyOutSummaryRedirectParameters = ['reportId' => $reportId, 'from' => 'does_money_out_exist'];

                return empty($softDeletedMoneyOutTransactionIds) ? $this->redirectToRoute('money_out_step', $moneyOutStepRedirectParameters)
                : $this->redirectToRoute('money_out_summary', $moneyOutSummaryRedirectParameters);
            } elseif ('No' === $answer && 'summary' === $fromPage) {
                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedMoneyOutTransactionIds, $report);

                return $this->redirectToRoute('no_money_out_exists', ['reportId' => $reportId, 'from' => 'does_money_out_exist']);
            } else {
                return $this->redirectToRoute('no_money_out_exists', ['reportId' => $reportId, 'from' => 'does_money_out_exist']);
            }
        }

        $backLink = $this->generateUrl('money_out', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    private function handleSoftDeletionOfMoneyTransactionItems(string $answer, array $softDeletedTransactionIds, $report): void
    {
        $reportId = $report->getId();

        if ('Yes' === $answer) {
            // undelete soft deleted items if present
            foreach ($softDeletedTransactionIds as $transactionId) {
                $this->restClient->put('/report/' . $reportId . '/money-transaction/soft-delete/' . $transactionId, ['transactionSoftDelete']);
            }
        } else {
            // soft delete items
            $transactions = $report->getMoneyTransactionsOut();
            if (!empty($transactions)) {
                foreach ($transactions as $t) {
                    $this->restClient->put('/report/' . $reportId . '/money-transaction/soft-delete/' . $t->getId(), ['transactionSoftDelete']);
                }
            }
        }
    }

    #[Route(path: '/report/{reportId}/money-out/no-money-out-exists', name: 'no_money_out_exists')]
    #[Template('@App/Report/MoneyOut/noMoneyOutToReport.html.twig')]
    public function noMoneyOutToReport(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(NoMoneyOutType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $report = $validatingForm->getObjectOrThrow(null, Report::class);
            $answer = $validatingForm->getStringOrNull('reasonForNoMoneyOut');

            $report->setReasonForNoMoneyOut($answer);
            $report->getStatus()->setMoneyOutState(Status::STATE_DONE);
            $this->restClient->put('report/' . $reportId, $report, ['reasonForNoMoneyOut']);

            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_out_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/report/{reportId}/money-out/step{step}/{transactionId}', name: 'money_out_step', requirements: ['step' => '\d+'])]
    #[Template('@App/Report/MoneyOut/step.html.twig')]
    public function stepAction(
        Request $request,
        int $reportId,
        int $step,
        AuthorizationCheckerInterface $authorizationChecker,
        ?int $transactionId = null
    ): RedirectResponse|array {
        $result = match ($step) {
            1 => $this->step1($request, $reportId, $authorizationChecker, $transactionId),
            2 => $this->step2($request, $reportId, $authorizationChecker, $transactionId),
            default => $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]),
        };

        if ($result instanceof RedirectResponse) {
            return $result;
        }

        [$transaction, $report, $form, $stepRedirector] = $result;
        return [
            'transaction' => $transaction,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
            'categoriesGrouped' => MoneyTransaction::getCategoriesGrouped('out'),
        ];
    }

    #[Route(path: '/report/{reportId}/money-out/summary', name: 'money_out_summary')]
    #[Template('@App/Report/MoneyOut/summary.html.twig')]
    public function summaryAction(Request $request, int $reportId): RedirectResponse|array
    {

        $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyOutState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_out', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    #[Route(path: '/report/{reportId}/money-out/{transactionId}/delete', name: 'money_out_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, string $transactionId, TranslatorInterface $translator): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        foreach ($report->getMoneyTransactionsOut() as $t) {
            if ($t->getId() === $transactionId) {
                $transaction = $t;
                break;
            }
        }

        if (!isset($transaction)) {
            throw $this->createNotFoundException('Transaction not found');
        }

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('/report/' . $reportId . '/money-transaction/' . $transactionId);

            $this->addFlash('notice', $this->translator->trans('notices.entry.deleted', domain: 'report-money-out'));

            return $this->redirect($this->generateUrl('money_out_summary', ['reportId' => $reportId]));
        }

        $categoryKey = 'form.category.entries.' . $transaction->getCategory() . '.label';

        $summary = [
            ['label' => 'deletePage.summary.category', 'value' => $translator->trans($categoryKey, [], 'report-money-transaction')],
            ['label' => 'deletePage.summary.description', 'value' => $transaction->getDescription()],
            ['label' => 'deletePage.summary.amount', 'value' => $transaction->getAmount(), 'format' => 'money'],
        ];

        if ($report->canLinkToBankAccounts() && $transaction->getBankAccount()) {
            $summary[] = ['label' => 'deletePage.summary.bankAccount', 'value' => $transaction->getBankAccount()->getNameOneLine()];
        }

        return [
            'translationDomain' => 'report-money-out',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('money_out_summary', ['reportId' => $reportId]),
        ];
    }

    protected function getSectionId(): string
    {
        return 'moneyOut';
    }


    /**
     * @return RedirectResponse|array{MoneyTransaction, Report, FormInterface, StepRedirector}
     */
    private function step1(
        Request $request,
        int $reportId,
        AuthorizationCheckerInterface $authorizationChecker,
        ?int $transactionId = null
    ): RedirectResponse|array {
        // common vars and data
        /**
         * @var array $dataFromUrl
         */
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

        $stepRedirector = $this->stepRedirector
            ->setRoutes('does_money_out_exist', 'money_out_step', 'money_out_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep(1)->setTotalSteps(self::TOTAL_STEPS)
            ->setRouteBaseParams(['reportId' => $reportId, 'transactionId' => $transactionId]);

        $transaction = $this->acquireTransactions($transactionId, $report);

        // add URL-data into model
        isset($dataFromUrl['category']) && $transaction->setCategory($dataFromUrl['category']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // create and handle form
        $form = $this->createForm(
            MoneyTransactionType::class,
            $transaction,
            [
                'step' => 1,
                'type' => 'out',
                'selectedCategory' => $transaction->getCategory(),
                'authChecker' => $authorizationChecker,
                'report' => $report,
            ]
        );
        $form->handleRequest($request);

        /** @var SubmitButton $saveBtn */
        $saveBtn = $form->get('save');

        if ($saveBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // unset from page to prevent step redirector skipping step 2
            $stepRedirector->setFromPage(null);

            $stepUrlData['category'] = $transaction->getCategory();

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData,
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            $transaction,
            $report,
            $form,
            $stepRedirector
        ];
    }

    private function step2(
        Request $request,
        int $reportId,
        AuthorizationCheckerInterface $authorizationChecker,
        ?int $transactionId = null
    ): RedirectResponse|array {
        // common vars and data
        /**
         * @var array $dataFromUrl
         */
        $dataFromUrl = $request->get('data') ?: [];
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

        $stepRedirector = $this->stepRedirector
            ->setRoutes('does_money_out_exist', 'money_out_step', 'money_out_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep(2)->setTotalSteps(self::TOTAL_STEPS)
            ->setRouteBaseParams(['reportId' => $reportId, 'transactionId' => $transactionId]);

        $transaction = $this->acquireTransactions($transactionId, $report);

        // add URL-data into model
        isset($dataFromUrl['category']) && $transaction->setCategory($dataFromUrl['category']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // create and handle form
        $form = $this->createForm(
            MoneyTransactionType::class,
            $transaction,
            [
                'step' => 2,
                'type' => 'out',
                'selectedCategory' => $transaction->getCategory(),
                'authChecker' => $authorizationChecker,
                'report' => $report,
            ]
        );
        if ($transactionId === null) {
            $form->add('addAnother', AddAnotherThingType::class);
        }
        $form->handleRequest($request);

        /** @var SubmitButton $saveBtn */
        $saveBtn = $form->get('save');

        if ($saveBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step

            if ($transactionId === null) { // add
                $this->addFlash('notice', $this->translator->trans('notices.entry.added', domain: 'report-money-out'));
                $this->restClient->post('/report/' . $reportId . '/money-transaction', $transaction, ['transaction', 'account']);

                if ($form['addAnother']?->getData() === 'yes') {
                    return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1]);
                }
            } else { // edit
                $this->addFlash('notice', $this->translator->trans('notices.entry.edited', domain: 'report-money-out'));
                $this->restClient->put('/report/' . $reportId . '/money-transaction/' . $transactionId, $transaction, ['transaction', 'account']);
            }
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        return [
            $transaction,
            $report,
            $form,
            $stepRedirector
        ];
    }

    /**
     * create (add mode) or load transaction (edit mode)
     */
    private function acquireTransactions(?int $transactionId, Report $report): MoneyTransaction
    {
        if ($transactionId !== null) {
            $transaction = array_filter($report->getMoneyTransactionsOut(), function ($t) use ($transactionId): bool {
                if ($t->getBankAccount() instanceof BankAccount) {
                    $t->setBankAccountId($t->getBankAccount()->getId());
                }

                return $t->getId() == $transactionId;
            });
            $transaction = array_shift($transaction);
        } else {
            $transaction = new MoneyTransaction();
        }

        if (is_null($transaction)) {
            throw $this->createNotFoundException();
        }
        return $transaction;
    }
}
