<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\MoneyTransfer;
use App\Entity\Report\Status;
use App\Form\AddAnotherRecordType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\MoneyTransferType;
use App\Form\YesNoType;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoneyTransferController extends AbstractController
{
    private static array $jmsGroups = [
        'money-transfer',
        'account',
        'money-transfer-state',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly StepRedirector $stepRedirector,
    ) {
    }

    #[Route(path: '/report/{reportId}/money-transfers', name: 'money_transfers')]
    #[Template('@App/Report/MoneyTransfer/start.html.twig')]
    public function startAction(int $reportId): Response|RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (!$report->enoughBankAccountForTransfers()) {
            return $this->render('@App/Report/MoneyTransfer/error.html.twig', [
                'error' => 'atLeastTwoBankAccounts',
                'report' => $report,
            ]);
        }

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getMoneyTransferState()['state']) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/money-transfers/exist', name: 'money_transfers_exist')]
    #[Template('@App/Report/MoneyTransfer/exist.html.twig')]
    public function existAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(YesNoType::class, $report, [
            'field' => 'noTransfersToAdd',
            'translation_domain' => 'report-money-transfer',
            'choices' => ['Yes' => 0, 'No' => 1],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            switch ($report->getNoTransfersToAdd()) {
                case 0:
                    return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'step' => 1]);
                case 1:
                    $this->restClient->put("report/$reportId", $report, ['money-transfers-no-transfers']);

                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('money_transfers', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/money-transfers/step{step}/{transferId}', name: 'money_transfers_step')]
    #[Template('@App/Report/MoneyTransfer/step.html.twig')]
    public function stepAction(Request $request, int $reportId, int $step, ?int $transferId = null): RedirectResponse|array
    {
        $totalSteps = 1;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('money_transfers', 'money_transfers_step', 'money_transfers_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'transferId' => $transferId]);

        // create (add mode) or load transaction (edit mode)
        if ($transferId) {
            $transfer = $report->getMoneyTransferWithId($transferId);

            if (is_null($transfer)) {
                throw $this->createNotFoundException('Transfer not found');
            }

            $transfer->setAccountFromId($transfer->getAccountFrom()->getId());
            $transfer->setAccountToId($transfer->getAccountTo()->getId());
        } else {
            $transfer = new MoneyTransfer();
        }

        // add URL-data into model
        if (isset($dataFromUrl['from-id']) && isset($dataFromUrl['to-id'])) {
            $transfer->setAccountFromId($dataFromUrl['from-id']);
            $transfer->setAccountFrom($report->getBankAccountById($dataFromUrl['from-id']));
            $transfer->setAccountToId($dataFromUrl['to-id']);
            $transfer->setAccountTo($report->getBankAccountById($dataFromUrl['to-id']));
        }
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl,
        ]);

        // create and handle form
        $form = $this->createForm(MoneyTransferType::class, $transfer, ['banks' => $report->getBankAccounts()]);
        $form->handleRequest($request);

        /** @var SubmitButton $submitBtn */
        $submitBtn = $form->get('save');

        if ($submitBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step

            $stepUrlData['from-id'] = $transfer->getAccountFromId();
            $stepUrlData['to-id'] = $transfer->getAccountToId();

            if ($transferId) { // edit
                $this->addFlash(
                    'notice',
                    'Entry edited'
                );
                $this->restClient->put('/report/' . $reportId . '/money-transfers/' . $transferId, $transfer, ['money-transfer']);

                return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }

            // add
            $this->restClient->post('/report/' . $reportId . '/money-transfers', $transfer, ['money-transfer']);

            if ('yes' === $form['addAnother']->getData()) {
                return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'from' => 'another', 'step' => 1]);
            }

            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'transfer' => $transfer,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }

    #[Route(path: '/report/{reportId}/money-transfers/summary', name: 'money_transfers_summary')]
    #[Template('@App/Report/MoneyTransfer/summary.html.twig')]
    public function summaryAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (Status::STATE_NOT_STARTED == $report->getStatus()->getMoneyTransferState()['state']) {
            return $this->redirect($this->generateUrl('money_transfers', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    #[Route(path: '/report/{reportId}/money-transfers/{transferId}/delete', name: 'money_transfers_delete')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteAction(Request $request, int $reportId, int $transferId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/report/$reportId/money-transfers/$transferId");

            $this->addFlash(
                'notice',
                'Money transfer deleted'
            );

            return $this->redirect($this->generateUrl('money_transfers_summary', ['reportId' => $reportId]));
        }

        $transfer = $report->getMoneyTransferWithId($transferId);

        if (is_null($transfer)) {
            throw $this->createNotFoundException('Transfer not found');
        }

        return [
            'translationDomain' => 'report-money-transfer',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.accountFrom', 'value' => $transfer->getAccountFrom()->getNameOneLine()],
                ['label' => 'deletePage.summary.accountTo', 'value' => $transfer->getAccountTo()->getNameOneLine()],
                ['label' => 'deletePage.summary.amount', 'value' => $transfer->getAmount(), 'format' => 'money'],
                ['label' => 'deletePage.summary.description', 'value' => $transfer->getDescription()],
            ],
            'backLink' => $this->generateUrl('money_transfers_summary', ['reportId' => $reportId]),
        ];
    }

    protected function getSectionId(): string
    {
        return 'moneyTransfers';
    }
}
