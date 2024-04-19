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

class MoneyInController extends AbstractController
{
    private static $jmsGroups = [
        'transactionsIn',
        'money-in-state',
        'account',
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
        StepRedirector $stepRedirector
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->stepRedirector = $stepRedirector;
    }

    /**
     * @Route("/report/{reportId}/money-in", name="money_in")
     * @Template("@App/Report/MoneyIn/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyInState()['state']) {
            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in/exist", name="does_money_in_exist")
     * @Template("@App/Report/MoneyIn/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\DoesMoneyInExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['moneyInExists']->getData();
            $fromPage = $request->get('from');

            $report->setMoneyInExists($answer);
            $this->restClient->put('report/'.$reportId, $report, ['doesMoneyInExist']);

            // retrieve soft deleted transaction ids if present
            $softDeletedTransactionIds = $this->restClient->get('/report/'.$reportId.'/money-transaction/get-soft-delete', 'array');

            if ('Yes' === $answer && 'summary' != $fromPage) {
                $report->setReasonForNoMoneyIn(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyIn']);

                return $this->redirectToRoute('money_in_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_in_exist']);
            } elseif ('Yes' === $answer && 'summary' === $fromPage) {
                $report->setReasonForNoMoneyIn(null);
                $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyIn']);

                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedTransactionIds, $report);

                $moneyInStepRedirectParameters = ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_in_exist'];
                $moneyInSummaryRedirectParameters = ['reportId' => $reportId, 'from' => 'does_money_in_exist'];

                return empty($softDeletedTransactionIds) ? $this->redirectToRoute('money_in_step', $moneyInStepRedirectParameters)
                : $this->redirectToRoute('money_in_summary', $moneyInSummaryRedirectParameters);
            } elseif ('No' === $answer && 'summary' === $fromPage) {
                $this->handleSoftDeletionOfMoneyTransactionItems($answer, $softDeletedTransactionIds, $report);

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
            $transactions = $report->getMoneyTransactionsIn();
            if (!empty($transactions)) {
                foreach ($transactions as $t) {
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/soft-delete/'.$t->getId(), ['transactionSoftDelete']);
                }
            }
        }
    }

    /**
     * @Route("/report/{reportId}/money-in/no-money-in-exists", name="no_money_in_exists")
     * @Template("@App/Report/MoneyIn/noMoneyInToReport.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function noMoneyInToReport(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\NoMoneyInType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['reasonForNoMoneyIn']->getData();

            $report->setReasonForNoMoneyIn($answer);
            $report->getStatus()->setMoneyInState(Status::STATE_DONE);
            $this->restClient->put('report/'.$reportId, $report, ['reasonForNoMoneyIn']);

            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        $backLink = $this->generateUrl('does_money_in_exist', ['reportId' => $reportId]);

        return [
            'backLink' => $backLink,
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in/step{step}/{transactionId}", name="money_in_step", requirements={"step":"\d+"})
     * @Template("@App/Report/MoneyIn/step.html.twig")
     *
     * @param null $transactionId
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $reportId, $step, AuthorizationCheckerInterface $authorizationChecker, $transactionId = null)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('does_money_in_exist', 'money_in_step', 'money_in_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'transactionId' => $transactionId]);

        // create (add mode) or load transaction (edit mode)
        if ($transactionId) {
            $transaction = array_filter($report->getMoneyTransactionsIn(), function ($t) use ($transactionId) {
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

        // add URL-data into model
        isset($dataFromUrl['category']) && $transaction->setCategory($dataFromUrl['category']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // crete and handle form
        $form = $this->createForm(
            FormDir\Report\MoneyTransactionType::class,
            $transaction,
            [
                'step' => $step,
                'type' => 'in',
                'selectedCategory' => $transaction->getCategory(),
                'authChecker' => $authorizationChecker,
                'report' => $report,
            ]
        );
        $form->handleRequest($request);

        /** @var SubmitButton $saveButton */
        $saveButton = $form->get('save');
        if ($saveButton->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if (1 == $step) {
                // unset from page to prevent step redirector skipping step 2
                $stepRedirector->setFromPage(null);

                $stepUrlData['category'] = $transaction->getCategory();
            } elseif ($step == $totalSteps) {
                if ($transactionId) { // edit
                    $this->addFlash(
                        'notice',
                        'Entry edited'
                    );
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/'.$transactionId, $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
                } else { // add
                    $this->restClient->post('/report/'.$reportId.'/money-transaction', $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_in_add_another', ['reportId' => $reportId]);
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
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in/add_another", name="money_in_add_another")
     * @Template("@App/Report/MoneyIn/addAnother.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-money-transaction']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_in_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'money_in_add_another']);
                case 'no':
                    return $this->redirectToRoute('money_in_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in/summary", name="money_in_summary")
     * @Template("@App/Report/MoneyIn/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyInState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('money_in', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in/{transactionId}/delete", name="money_in_delete")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId, TranslatorInterface $translator)
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

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('/report/'.$reportId.'/money-transaction/'.$transactionId);

            $this->addFlash(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_in_summary', ['reportId' => $reportId]));
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
            'translationDomain' => 'report-money-in',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('money_in_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyIn';
    }
}
