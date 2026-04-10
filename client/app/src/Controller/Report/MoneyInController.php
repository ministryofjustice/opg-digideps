<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Report;

use OPG\Digideps\Frontend\Controller\AbstractController;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\Status;
use OPG\Digideps\Frontend\Form\AddAnotherThingType;
use OPG\Digideps\Frontend\Form\ConfirmDeleteType;
use OPG\Digideps\Frontend\Form\Report\DoesMoneyInExistType;
use OPG\Digideps\Frontend\Form\Report\MoneyTransactionType;
use OPG\Digideps\Frontend\Form\Report\NoMoneyInType;
use OPG\Digideps\Frontend\Service\Client\Internal\ReportApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\StepRedirector;
use OPG\Digideps\Common\Validating\ValidatingForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MoneyInController extends AbstractController
{
    private static array $jmsGroups = [
        'transactionsIn',
        'money-in-state',
        'account',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    #[Route(path: '/report/{reportId}/money-in', name: 'money_in')]
    #[Template('@App/Report/MoneyIn/start.html.twig')]
    public function startAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = $report->getStatus()->getMoneyInState();
        if (Status::STATE_NOT_STARTED != $status['state']) {
            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/money-in/exist', name: 'does_money_in_exist')]
    #[Template('@App/Report/MoneyIn/exist.html.twig')]
    public function existAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(DoesMoneyInExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $answer = $validatingForm->getStringOrNull('moneyInExists');
            $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

            $report->setMoneyInExists($answer);
            $this->restClient->put('report/' . $reportId, $report, ['doesMoneyInExist']);

            // retrieve soft deleted transaction ids if present and handle money in ids only
            /** @var MoneyTransaction[] $softDeletedTransactionIds */
            $softDeletedTransactionIds = $this->restClient->get("/report/$reportId/money-transaction/get-soft-delete", 'Report\MoneyTransaction[]');

            $softDeletedMoneyInTransactionIds = [];
            foreach ($softDeletedTransactionIds as $softDeletedTransactionId) {
                if ('in' == $softDeletedTransactionId->getType()) {
                    $softDeletedMoneyInTransactionIds[] = $softDeletedTransactionId->getId();
                }
            }

            if ('Yes' === $answer && 'summary' != $fromPage) {
                $report->setReasonForNoMoneyIn(null);
                $this->restClient->put('report/' . $reportId, $report, ['reasonForNoMoneyIn']);

                return $this->redirectToRoute('money_in_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_in_exist']);
            } elseif ('Yes' === $answer && 'summary' === $fromPage) {
                $report->setReasonForNoMoneyIn(null);
                $this->restClient->put('report/' . $reportId, $report, ['reasonForNoMoneyIn']);

                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedMoneyInTransactionIds, $report);

                $moneyInStepRedirectParameters = ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_in_exist'];
                $moneyInSummaryRedirectParameters = ['reportId' => $reportId, 'from' => 'does_money_in_exist'];

                return empty($softDeletedMoneyInTransactionIds) ? $this->redirectToRoute('money_in_step', $moneyInStepRedirectParameters)
                : $this->redirectToRoute('money_in_summary', $moneyInSummaryRedirectParameters);
            } elseif ('No' === $answer && 'summary' === $fromPage) {
                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedMoneyInTransactionIds, $report);

                return $this->redirectToRoute('no_money_in_exists', ['reportId' => $reportId, 'from' => 'does_money_in_exist']);
            } else {
                return $this->redirectToRoute('no_money_in_exists', ['reportId' => $reportId, 'from' => 'does_money_in_exist']);
            }
        }

        $backLink = $this->generateUrl('money_in', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    private function handleSoftDeletionOfMoneyTransactionItems(string $answer, array $softDeletedTransactionIds, Report $report): void
    {
        $reportId = $report->getId();

        if ('Yes' === $answer) {
            // undelete soft deleted items if present
            foreach ($softDeletedTransactionIds as $transactionId) {
                $this->restClient->put('/report/' . $reportId . '/money-transaction/soft-delete/' . $transactionId, ['transactionSoftDelete']);
            }
        } else {
            // soft delete items
            $transactions = $report->getMoneyTransactionsIn();
            if (!empty($transactions)) {
                foreach ($transactions as $t) {
                    $this->restClient->put('/report/' . $reportId . '/money-transaction/soft-delete/' . $t->getId(), ['transactionSoftDelete']);
                }
            }
        }
    }

    #[Route(path: '/report/{reportId}/money-in/no-money-in-exists', name: 'no_money_in_exists')]
    #[Template('@App/Report/MoneyIn/noMoneyInToReport.html.twig')]
    public function noMoneyInToReport(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(NoMoneyInType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $answer = $validatingForm->getStringOrNull('reasonForNoMoneyIn');

            $report->setReasonForNoMoneyIn($answer);
            $report->getStatus()->setMoneyInState(Status::STATE_DONE);
            $this->restClient->put('report/' . $reportId, $report, ['reasonForNoMoneyIn']);

            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_in_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/report/{reportId}/money-in/step{step}/{transactionId}', name: 'money_in_step', requirements: ['step' => '\d+'])]
    #[Template('@App/Report/MoneyIn/step.html.twig')]
    public function stepAction(
        Request $request,
        int $reportId,
        int $step,
        AuthorizationCheckerInterface $authorizationChecker,
        ?int $transactionId = null
    ): RedirectResponse|array {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        /** @var array $dataFromUrl */
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));

        $stepRedirector = $this->stepRedirector
            ->setRoutes('does_money_in_exist', 'money_in_step', 'money_in_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'transactionId' => $transactionId]);

        // create (add mode) or load transaction (edit mode)
        $addingItem = false;
        if ($transactionId) {
            $transaction = array_filter($report->getMoneyTransactionsIn(), function ($t) use ($transactionId): bool {
                if ($t->getBankAccount() instanceof BankAccount) {
                    $t->setBankAccountId($t->getBankAccount()->getId());
                }

                return $t->getId() == $transactionId;
            });
            $transaction = array_shift($transaction);
        } else {
            $transaction = new MoneyTransaction();
            $addingItem = true;
        }

        if (is_null($transaction)) {
            throw $this->createNotFoundException();
        }

        // add URL-data into model
        isset($dataFromUrl['category']) && $transaction->setCategory($dataFromUrl['category']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(
            MoneyTransactionType::class,
            $transaction,
            [
                'step' => $step,
                'type' => 'in',
                'selectedCategory' => $transaction->getCategory(),
                'authChecker' => $authorizationChecker,
                'report' => $report,
            ]
        );

        // if we are adding an item and on the second page, we need the "add another" option
        if ($addingItem && 2 === $step) {
            $form->add('addAnother', AddAnotherThingType::class);
        }

        $form->handleRequest($request);

        $validatingForm = new ValidatingForm($form);
        $saveButton = $validatingForm->getObjectOrThrow('save', SubmitButton::class);

        if ($saveButton->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if (1 === $step) {
                // unset from page to prevent step redirector skipping step 2
                $stepRedirector->setFromPage(null);

                $stepUrlData['category'] = $transaction->getCategory();
            } elseif ($step === $totalSteps) {
                if ($addingItem) {
                    // add
                    $this->restClient->post("/report/$reportId/money-transaction", $transaction, ['transaction', 'account']);

                    $validatingForm = new ValidatingForm($form);
                    $addAnother = $validatingForm->getStringOrNull('addAnother');
                    // check whether we are adding another after this one and redirect appropriately
                    switch ($addAnother) {
                        case 'yes':
                            return $this->redirectToRoute('money_in_step', ['step' => 1, 'reportId' => $reportId]);
                        case 'no':
                            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
                    }
                } else {
                    // edit
                    $this->addFlash(
                        'notice',
                        'Entry edited'
                    );

                    $this->restClient->put("/report/$reportId/money-transaction/$transactionId", $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData,
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'transaction' => $transaction,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
            'categoriesGrouped' => MoneyTransaction::getCategoriesGrouped('in'),
            'transactionId' => $transaction->getId(),
        ];
    }

    #[Route(path: '/report/{reportId}/money-in/summary', name: 'money_in_summary')]
    #[Template('@App/Report/MoneyIn/summary.html.twig')]
    public function summaryAction(Request $request, int $reportId): RedirectResponse|array
    {
        $fromPage = $request->query->getString('from', $request->getPayload()->getString('from'));
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = $report->getStatus()->getMoneyInState();
        if (Status::STATE_NOT_STARTED == $status['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_in', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    #[Route(path: '/report/{reportId}/money-in/{transactionId}/delete', name: 'money_in_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, string $transactionId, TranslatorInterface $translator): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        foreach ($report->getMoneyTransactionsIn() as $t) {
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

            $this->addFlash(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_in_summary', ['reportId' => $reportId]));
        }

        $categoryKey = 'form.category.entries.' . $transaction->getCategory() . '.label';
        $summary = [
            ['label' => 'deletePage.summary.category', 'value' => $translator->trans($categoryKey, [], 'report-money-transaction')],
            ['label' => 'deletePage.summary.description', 'value' => $transaction->getDescription()],
            ['label' => 'deletePage.summary.amount', 'value' => $transaction->getAmount(), 'format' => 'money'],
        ];

        /** @var BankAccount $bankAccount */
        $bankAccount = $transaction->getBankAccount();
        if ($report->canLinkToBankAccounts() && $transaction->getBankAccount()) {
            $summary[] = ['label' => 'deletePage.summary.bankAccount', 'value' => $bankAccount->getNameOneLine()];
        }

        return [
            'translationDomain' => 'report-money-in',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('money_in_summary', ['reportId' => $reportId]),
        ];
    }

    protected function getSectionId(): string
    {
        return 'moneyIn';
    }
}
