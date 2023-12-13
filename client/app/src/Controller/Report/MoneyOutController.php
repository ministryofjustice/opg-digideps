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
     * @Route("/report/{reportId}/money-out", name="money_out")
     *
     * @Template("@App/Report/MoneyOut/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
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
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\DoesMoneyOutExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['doesMoneyOutExist']->getData();

            $report->setMoneyOutExists($answer);
            $this->restClient->put('report/'.$reportId, $report, ['doesMoneyOutExist']);

            if ('yes' === $answer) {
                return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1, 'from' => 'does_money_out_exist']);
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

    /**
     * @Route("/report/{reportId}/money-out/no-money-out-exists", name="no_money_out_exists")
     *
     * @Template("@App/Report/MoneyOut/noMoneyOutToReport.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function noMoneyOutToReport(Request $request, $reportId)
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
     * @Route("/report/{reportId}/money-out/step{step}/{transactionId}", name="money_out_step", requirements={"step":"\d+"})
     *
     * @Template("@App/Report/MoneyOut/step.html.twig")
     *
     * @param null $transactionId
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $reportId, $step, AuthorizationCheckerInterface $authorizationChecker, $transactionId = null)
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
        if ($transactionId) {
            $transaction = array_filter($report->getMoneyTransactionsOut(), function ($t) use ($transactionId) {
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
                if ($transactionId) { // edit
                    $this->addFlash(
                        'notice',
                        'Entry edited'
                    );
                    $this->restClient->put('/report/'.$reportId.'/money-transaction/'.$transactionId, $transaction, ['transaction', 'account']);

                    return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
                } else { // add
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
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyOutState()['state']) {
            return $this->redirectToRoute('money_out', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/{transactionId}/delete", name="money_out_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId, TranslatorInterface $translator)
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

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyOut';
    }
}
