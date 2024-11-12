<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrMoneyReceivedOnClientsBehalf;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Entity\Report\Status;
use App\Entity\User;
use App\Form\ConfirmDeleteType;
use App\Form\Report\ClientBenefitsCheckType;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ClientBenefitsCheckApi;
use App\Service\Client\Internal\MoneyReceivedOnClientsBehalfApi;
use App\Service\Client\Internal\NdrApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\StepRedirector;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        private ReportApi $reportApi,
        private ClientBenefitsCheckApi $benefitCheckApi,
        private StepRedirector $stepRedirector,
        private MoneyReceivedOnClientsBehalfApi $moneyTypeApi,
        private NdrApi $ndrApi,
        private ClientApi $clientApi
    ) {
    }

    /**
     * @Route("/{reportOrNdr}/{reportId}/client-benefits-check", name="client_benefits_check", requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * }))
     *
     * @Template("@App/Report/ClientBenefitsCheck/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function start(int $reportId, string $reportOrNdr)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = ('ndr' === $reportOrNdr) ? $this->ndrApi->getNdr($reportId, array_merge(self::$jmsGroups, ['ndr-client'])) :
            $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $status = ('ndr' === $reportOrNdr) ? $report->getStatusService()->getClientBenefitsCheckState()['state'] :
            $report->getStatus()->getClientBenefitsCheckState()['state'];

        if (Status::STATE_NOT_STARTED != $status) {
            return $this->redirectToRoute(
                'client_benefits_check_summary',
                ['reportId' => $reportId, 'reportOrNdr' => $reportOrNdr]
            );
        }

        return [
            'report' => $report,
            'reportOrNdr' => $reportOrNdr,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/{reportOrNdr}/{reportId}/client-benefits-check/step/{step}", name="client_benefits_check_step"), requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * }))
     *
     * @Template("@App/Report/ClientBenefitsCheck/step.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function step(Request $request, int $reportId, int $step, string $reportOrNdr)
    {
        $totalSteps = 3;

        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute(
                'client_benefits_check_summary',
                ['reportId' => $reportId, 'reportOrNdr' => $reportOrNdr]
            );
        }

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('client_benefits_check', 'client_benefits_check_step', 'client_benefits_check_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'reportOrNdr' => $reportOrNdr]);

        $report = ('ndr' === $reportOrNdr) ? $this->ndrApi->getNdr($reportId, array_merge(self::$jmsGroups, ['ndr-client', 'ndr-id', 'ndr'])) :
            $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ('ndr' === $reportOrNdr) {
            $clientBenefitsCheck = $report->getClientBenefitsCheck() ?: new NdrClientBenefitsCheck();
            $clientBenefitsCheck->setNdr($report);
        } else {
            $clientBenefitsCheck = $report->getClientBenefitsCheck() ?: new ClientBenefitsCheck();
            $clientBenefitsCheck->setReport($report);
        }

        if (3 === $step) {
            if (empty($clientBenefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf())) {
                $clientBenefitsCheck->setTypesOfMoneyReceivedOnClientsBehalf(new ArrayCollection());
            }

            $income = ('ndr' === $reportOrNdr) ? new NdrMoneyReceivedOnClientsBehalf() : new MoneyReceivedOnClientsBehalf();
            $clientBenefitsCheck->addTypeOfMoneyReceivedOnClientsBehalf($income);
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
                'data_class' => 'ndr' === $reportOrNdr ? NdrClientBenefitsCheck::class : ClientBenefitsCheck::class,
                'label_translation_parameters' => ['clientFirstname' => $report->getClient()->getFirstname()],
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ClientBenefitsCheck|NdrClientBenefitsCheck $formData */
            $clientBenefitsCheck = $form->getData();
            'ndr' === $reportOrNdr ? $clientBenefitsCheck->setNdr($report) : $clientBenefitsCheck->setReport($report);

            if ($form->has('addAnother') && $form->get('addAnother')->isClicked()) {
                $redirectRoute = $request->getUri();
            } else {
                $stepToRedirectFrom = $this->incomeNotReceivedByOthers($form) ? $step + 1 : $step;
                $redirectRoute = $stepRedirector->setCurrentStep($stepToRedirectFrom)->getRedirectLinkAfterSaving(['reportOrNdr' => $reportOrNdr]);
            }

            if (is_null($clientBenefitsCheck->getId())) {
                $this->benefitCheckApi->post($clientBenefitsCheck);
            } else {
                $this->benefitCheckApi->put($clientBenefitsCheck);
            }

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

    private function incomeNotReceivedByOthers(FormInterface $form)
    {
        $notYesStatuses = [ClientBenefitsCheck::OTHER_MONEY_NO, ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW];

        return $form->has('doOthersReceiveMoneyOnClientsBehalf')
            && in_array($form->get('doOthersReceiveMoneyOnClientsBehalf')->getData(), $notYesStatuses);
    }

    /**
     * @Route("/{reportOrNdr}/{reportId}/client-benefits-check/summary", name="client_benefits_check_summary"), requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * }))
     *
     * @Template("@App/Report/ClientBenefitsCheck/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summary(int $reportId, string $reportOrNdr)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = ('ndr' === $reportOrNdr) ? $this->ndrApi->getNdr($reportId, array_merge(self::$jmsGroups, ['ndr-client'])) :
            $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
            'reportOrNdr' => $reportOrNdr,
            'showActions' => true,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/{reportOrNdr}/{reportId}/client-benefits-check/remove/money-type/{moneyTypeId}", name="client_benefits_check_remove_money_type", requirements={
     *   "reportOrNdr" = "(report|ndr)"
     * })))
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function removeIncomeType(Request $request, int $reportId, string $moneyTypeId, string $reportOrNdr)
    {
        $report = ('ndr' === $reportOrNdr) ? $this->ndrApi->getNdr($reportId, self::$jmsGroups) :
            $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        foreach ($report->getClientBenefitsCheck()->getTypesOfMoneyReceivedOnClientsBehalf() as $moneyType) {
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
            $this->moneyTypeApi->deleteMoneyType($reportOrNdr, $moneyTypeId);

            $this->addFlash(
                'notice',
                'Money type deleted'
            );

            return $this->redirect($this->generateUrl(
                'client_benefits_check_summary',
                ['reportId' => $reportId, 'reportOrNdr' => $reportOrNdr])
            );
        }

        $summary = [
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column1Title', 'value' => $moneyTypeToDelete->getMoneyType()],
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column2Title', 'value' => $moneyTypeToDelete->getWhoReceivedMoney()],
            ['label' => 'summaryPage.table.moneyOtherPeopleReceive.column3Title', 'value' => '£'.$moneyTypeToDelete->getAmount()],
        ];

        return [
            'translationDomain' => 'report-client-benefits-check',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl(
                'client_benefits_check_summary',
                ['reportId' => $reportId, 'reportOrNdr' => $reportOrNdr]
            ),
        ];
    }
}
