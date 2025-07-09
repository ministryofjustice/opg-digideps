<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\Status;
use App\Form as FormDir;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MoneyOutController extends AbstractController
{
    private static $jmsGroups = [
        'transactionsOut',
        'money-out-state',
        'account',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    /**
     * @Route("/report/{reportId}/money-out", name="money_out")
     *
     * @Template("@App/Report/MoneyOut/start.html.twig")
     */
    public function startAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyOutState()['state']) {
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/exist", name="does_money_out_exist")
     *
     * @Template("@App/Report/MoneyOut/exist.html.twig")
     */
    public function existAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\DoesMoneyOutExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['moneyOutExists']->getData();
            $fromPage = $request->get('from');

            $report->setMoneyOutExists($answer);
            $this->restClient->put('report/'.$reportId, $report, ['doesMoneyOutExist']);

            // retrieve soft deleted transaction ids if present and handle money out ids only
            $softDeletedTransactionIds = $this->restClient->get('/report/'.$reportId.'/money-transaction/get-soft-delete', 'Report\MoneyTransaction[]');

            $softDeletedMoneyOutTransactionIds = [];
            foreach ($softDeletedTransactionIds as $softDeletedTransactionId) {
                if ('out' == $softDeletedTransactionId->getType()) {
                    $softDeletedMoneyOutTransactionIds[] = $softDeletedTransactionId->getId();
                }
            }

            if ('Yes' === $answer && 'summary' != $fromPage) {
                $report->setReasonForNoMoneyOut(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyOut']);

                return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_out_exist']);
            } elseif ('Yes' === $answer && 'summary' === $fromPage) {
                $report->setReasonForNoMoneyOut(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyOut']);

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

    private function handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedTransactionIds, $report): void
    {
        $reportId = $report->getId();

        if ('Yes' === $answer) {
            // undelete soft deleted items if present
            if (!empty($softDeletedTransactionIds)) {
                foreach ($softDeletedTransactionIds as $transactionId) {
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/soft-delete/'.$transactionId, ['transactionSoftDelete']);
                }
            }
        } else {
            // soft delete items
            $transactions = $report->getMoneyTransactionsOut();
            if (!empty($transactions)) {
                foreach ($transactions as $t) {
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/soft-delete/'.$t->getId(), ['transactionSoftDelete']);
                }
            }
        }
    }

    /**
     * @Route("/report/{reportId}/money-out/no-money-out-exists", name="no_money_out_exists")
     *
     * @Template("@App/Report/MoneyOut/noMoneyOutToReport.html.twig")
     */
    public function noMoneyOutToReport(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\NoMoneyOutType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['reasonForNoMoneyOut']->getData();

            $report->setReasonForNoMoneyOut($answer);
            $report->getStatus()->setMoneyOutState(Status::STATE_DONE);
            $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyOut']);

            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_out_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/step{step}/{transactionId}", requirements={"step":"\d+"}, name="money_out_step")
     *
     * @Template("@App/Report/MoneyOut/step.html.twig")
     */
    public function stepAction(Request $request, int $reportId, int $step, AuthorizationCheckerInterface $authorizationChecker, ?int $transactionId = null): array|RedirectResponse
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('does_money_out_exist', 'money_out_step', 'money_out_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'transactionId' => $transactionId]);

        // create (add mode) or load transaction (edit mode)
        if (is_null($transactionId)) {
            $transaction = new MoneyTransaction();
        } else {
            $transaction = array_filter($report->getMoneyTransactionsOut(), function ($t) use ($transactionId) {
                if ($t->getBankAccount() instanceof BankAccount) {
                    $t->setBankAccountId($t->getBankAccount()->getId());
                }

                return $t->getId() == $transactionId;
            });
            $transaction = array_shift($transaction);
        }

        if (is_null($transaction)) {
            throw $this->createNotFoundException();
        }

        // add URL-data into model
        /** @var ?string $category */
        $category = $dataFromUrl['category'] ?? null;

        if (!is_null($category)) {
            $transaction->setCategory($category);
        }

        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(
            FormDir\Report\MoneyTransactionType::class,
            $transaction,
            [
                'step' => $step,
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
            // decide what data in the partial form needs to be passed to next step
            if (1 == $step) {
                // unset from page to prevent step redirector skipping step 2
                $stepRedirector->setFromPage(null);

                $stepUrlData['category'] = $transaction->getCategory();
            } elseif ($step == $totalSteps) {
                if ($transactionId) {
                    // edit
                    $this->addFlash(
                        'notice',
                        'Entry edited'
                    );
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/'.$transactionId, $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
                } else {
                    // add
                    $this->restClient->post('/report/'.$reportId.'/money-transaction', $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_out_add_another', ['reportId' => $reportId]);
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
            'categoriesGrouped' => MoneyTransaction::getCategoriesGrouped('out'),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/add_another", name="money_out_add_another")
     *
     * @Template("@App/Report/MoneyOut/addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-money-transaction']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/summary", name="money_out_summary")
     *
     * @Template("@App/Report/MoneyOut/summary.html.twig")
     */
    public function summaryAction(Request $request, int $reportId): array|RedirectResponse
    {
        $fromPage = $request->get('from');
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

    /**
     * @Route("/report/{reportId}/money-out/{transactionId}/delete", name="money_out_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     */
    public function deleteAction(Request $request, int $reportId, int $transactionId, TranslatorInterface $translator): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $transaction = null;
        foreach ($report->getMoneyTransactionsOut() as $t) {
            if ($t->getId() === "$transactionId") {
                $transaction = $t;
                break;
            }
        }

        if (is_null($transaction)) {
            throw $this->createNotFoundException('Transaction not found');
        }

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('/report/'.$reportId.'/money-transaction/'.$transactionId);

            $this->addFlash(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_out_summary', ['reportId' => $reportId]));
        }

        $categoryKey = 'form.category.entries.'.$transaction->getCategory().'.label';

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
}
