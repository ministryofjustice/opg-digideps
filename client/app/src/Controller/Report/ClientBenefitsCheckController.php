<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Entity\Report\Status;
use App\Form\AddAnotherThingType;
use App\Form\ConfirmDeleteType;
use App\Form\Report\ClientBenefitsCheckType;
use App\Service\Client\Internal\ClientBenefitsCheckApi;
use App\Service\Client\Internal\MoneyReceivedOnClientsBehalfApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\StepRedirector;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientBenefitsCheckController extends AbstractController
{
    private static array $jmsGroups = [
        'client-benefits-check',
        'client-benefits-check-state',
        'client-name',
    ];

    public function __construct(
        private readonly ReportApi $reportApi,
        private readonly ClientBenefitsCheckApi $benefitCheckApi,
        private readonly StepRedirector $stepRedirector,
        private readonly MoneyReceivedOnClientsBehalfApi $moneyTypeApi,
    ) {
    }

    #[Route(path: '/report/{reportId}/client-benefits-check', name: 'client_benefits_check')]
    #[Template('@App/Report/ClientBenefitsCheck/start.html.twig')]
    public function start(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = $report->getStatus()->getClientBenefitsCheckState()['state'];

        if (Status::STATE_NOT_STARTED != $status) {
            return $this->redirectToRoute(
                'client_benefits_check_summary',
                ['reportId' => $reportId]
            );
        }

        return ['report' => $report];
    }

    #[Route(path: '/report/{reportId}/client-benefits-check/step/{step}/{moneyTypeId}', name: 'client_benefits_check_step')]
    #[Template('@App/Report/ClientBenefitsCheck/step.html.twig')]
    public function step(Request $request, int $reportId, int $step, ?string $moneyTypeId = null): array|RedirectResponse
    {
        $totalSteps = 3;

        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute(
                'client_benefits_check_summary',
                ['reportId' => $reportId]
            );
        }

        /** @var string $fromPage */
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('client_benefits_check', 'client_benefits_check_step', 'client_benefits_check_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $clientBenefitsCheck = $report->getClientBenefitsCheck() ?: new ClientBenefitsCheck();
        $clientBenefitsCheck->setReport($report);

        if (3 === $step) {
            if (is_null($moneyTypeId)) {
                $income = new MoneyReceivedOnClientsBehalf();
                $clientBenefitsCheck->setTypesOfMoneyReceivedOnClientsBehalf(new ArrayCollection([$income]));
            } else {
                foreach ($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() as $moneyType) {
                    if ($moneyType->getId() === $moneyTypeId) {
                        $clientBenefitsCheck->setTypesOfMoneyReceivedOnClientsBehalf(
                            new ArrayCollection([$moneyType])
                        );
                        break;
                    }
                }
            }
        }

        // We only want to support deleting empty income types when there is at least one saved income type - otherwise validate the fields
        $allowDeleteEmpty = $clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf() instanceof ArrayCollection
            && count($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()) >= 2;

        $form = $this->createForm(
            ClientBenefitsCheckType::class,
            $clientBenefitsCheck,
            [
                'step' => $step,
                'allow_delete_empty' => $allowDeleteEmpty,
                'data_class' => ClientBenefitsCheck::class,
                'label_translation_parameters' => ['clientFirstname' => $report->getClient()->getFirstname()],
            ]
        );

        if ($step === 3) {
            $form->add('addAnother', AddAnotherThingType::class);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $formData->setReport($report);

            $addAnother = null;
            if ($form->has('addAnother')) {
                /** @var Form $addAnother */
                $addAnother = $form->get('addAnother');
            }

            if ($addAnother !== null && 'yes' === $addAnother->getData()) {
                $redirectRoute = $this->generateUrl(
                    'client_benefits_check_step',
                    ['reportId' => $reportId, 'step' => 3]
                );
            } else {
                $stepToRedirectFrom = $this->incomeNotReceivedByOthers($form) ? $step + 1 : $step;
                $redirectRoute = $stepRedirector->setCurrentStep($stepToRedirectFrom)
                    ->getRedirectLinkAfterSaving();
            }

            is_null($formData->getId()) ?
                $this->benefitCheckApi->post($formData) :
                $this->benefitCheckApi->put($formData);

            return $this->redirect($redirectRoute);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'step' => $step,
            'formAction' => is_null($report->getClientBenefitsCheck()) ? 'add' : 'edit',
            'backLink' => $this->stepRedirector->getBackLink(),
        ];
    }

    private function incomeNotReceivedByOthers(FormInterface $form): bool
    {
        $notYesStatuses = [ClientBenefitsCheck::OTHER_MONEY_NO, ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW];

        return $form->has('doOthersReceiveMoneyOnClientsBehalf')
            && in_array($form->get('doOthersReceiveMoneyOnClientsBehalf')->getData(), $notYesStatuses);
    }

    #[Route(path: '/report/{reportId}/client-benefits-check/summary', name: 'client_benefits_check_summary')]
    #[Template('@App/Report/ClientBenefitsCheck/summary.html.twig')]
    public function summary(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
            'showActions' => true,
        ];
    }

    #[Route(path: '/report/{reportId}/client-benefits-check/remove/money-type/{moneyTypeId}', name: 'client_benefits_check_remove_money_type')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function removeIncomeType(Request $request, int $reportId, string $moneyTypeId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        /** @var ArrayCollection<int,MoneyReceivedOnClientsBehalf> $typesOfMoniesReceived */
        $typesOfMoniesReceived = $report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf();
        foreach ($typesOfMoniesReceived as $moneyType) {
            if ($moneyType->getId() === $moneyTypeId) {
                $moneyTypeToDelete = $moneyType;
                break;
            }
        }

        if (!isset($moneyTypeToDelete)) {
            throw $this->createNotFoundException('Money type not found');
        }

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->moneyTypeApi->deleteMoneyType($moneyTypeId);

            $this->addFlash(
                'notice',
                'Money type deleted'
            );

            return $this->redirect($this->generateUrl(
                'client_benefits_check_summary',
                ['reportId' => $reportId]
            ));
        }

        $summary = [
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column1Title', 'value' => $moneyTypeToDelete->getMoneyType()],
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column2Title', 'value' => $moneyTypeToDelete->getWhoReceivedMoney()],
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column3Title', 'value' => '£' . $moneyTypeToDelete->getAmount()],
        ];

        return [
            'translationDomain' => 'report-client-benefits-check',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl(
                'client_benefits_check_summary',
                ['reportId' => $reportId]
            ),
        ];
    }
}
