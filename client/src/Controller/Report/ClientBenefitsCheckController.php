<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use App\Entity\Report\Status;
use App\Form\ConfirmDeleteType;
use App\Form\Report\ClientBenefitsCheckType;
use App\Service\Client\Internal\ClientBenefitsCheckApi;
use App\Service\Client\Internal\IncomeReceivedOnClientsBehalfApi;
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
    ];

    private ReportApi $reportApi;
    private ClientBenefitsCheckApi $benefitCheckApi;
    private StepRedirector $stepRedirector;
    private IncomeReceivedOnClientsBehalfApi $incomeTypeApi;

    public function __construct(
        ReportApi $reportApi,
        ClientBenefitsCheckApi $benefitCheckApi,
        StepRedirector $stepRedirector,
        IncomeReceivedOnClientsBehalfApi $incomeTypeApi
    ) {
        $this->reportApi = $reportApi;
        $this->benefitCheckApi = $benefitCheckApi;
        $this->stepRedirector = $stepRedirector;
        $this->incomeTypeApi = $incomeTypeApi;
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check", name="client_benefits_check")
     * @Template("@App/Report/ClientBenefitsCheck/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function start(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getClientBenefitsCheckState()['state']) {
            return $this->redirectToRoute('client_benefits_check_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check/step/{step}", name="client_benefits_check_step")
     * @Template("@App/Report/ClientBenefitsCheck/step.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function step(Request $request, int $reportId, int $step)
    {
        $totalSteps = 3;

        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('client_benefits_check_summary', ['reportId' => $reportId]);
        }

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('client_benefits_check', 'client_benefits_check_step', 'client_benefits_check_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $clientBenefitsCheck = $report->getClientBenefitsCheck() ?: new ClientBenefitsCheck();

        if (3 === $step) {
            if (empty($clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf())) {
                $clientBenefitsCheck->setTypesOfIncomeReceivedOnClientsBehalf(new ArrayCollection());
            }

            $clientBenefitsCheck->addTypeOfIncomeReceivedOnClientsBehalf(new IncomeReceivedOnClientsBehalf());
        }

        // We only want to support deleting empty income types when there is at least one saved income type - otherwise validate the fields
        $allowDeleteEmpty = $clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf() instanceof ArrayCollection &&
            count($clientBenefitsCheck->getTypesOfIncomeReceivedOnClientsBehalf()) >= 2;

        $form = $this->createForm(
            ClientBenefitsCheckType::class,
            $clientBenefitsCheck,
            [
                'step' => $step,
                'allow_delete_empty' => $allowDeleteEmpty,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ClientBenefitsCheck $formData */
            $clientBenefitsCheck = $form->getData();
            $clientBenefitsCheck->setReport($report);

            if ($form->has('addAnother') && $form->get('addAnother')->isClicked()) {
                $redirectRoute = $request->getUri();
            } else {
                $stepToRedirectFrom = $this->incomeNotReceivedByOthers($form) ? $step + 1 : $step;
                $redirectRoute = $stepRedirector->setCurrentStep($stepToRedirectFrom)->getRedirectLinkAfterSaving();
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
        $notYesStatuses = [ClientBenefitsCheck::OTHER_INCOME_NO, ClientBenefitsCheck::OTHER_INCOME_DONT_KNOW];

        return $form->has('doOthersReceiveIncomeOnClientsBehalf') &&
            in_array($form->get('doOthersReceiveIncomeOnClientsBehalf')->getData(), $notYesStatuses);
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check/summary", name="client_benefits_check_summary")
     * @Template("@App/Report/ClientBenefitsCheck/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summary(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check/remove/income-type/{incomeTypeId}", name="client_benefits_check_remove_income_type")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function removeIncomeType(Request $request, int $reportId, string $incomeTypeId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        foreach ($report->getClientBenefitsCheck()->getTypesOfIncomeReceivedOnClientsBehalf() as $incomeType) {
            if ($incomeType->getId() === $incomeTypeId) {
                $incomeTypeToDelete = $incomeType;
                break;
            }
        }

        if (!isset($incomeTypeToDelete)) {
            throw $this->createNotFoundException('Income type not found');
        }

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->incomeTypeApi->deleteIncomeType($incomeTypeId);

            $this->addFlash(
                'notice',
                'Income type deleted'
            );

            return $this->redirect($this->generateUrl('client_benefits_check_summary', ['reportId' => $reportId]));
        }

        $summary = [
            ['label' => 'summaryPage.table.incomeOtherPeopleReceive.column1Title', 'value' => $incomeTypeToDelete->getIncomeType()],
            ['label' => 'summaryPage.table.incomeOtherPeopleReceive.column2Title', 'value' => $incomeTypeToDelete->getAmount()],
        ];

        return [
            'translationDomain' => 'report-client-benefits-check',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('client_benefits_check_summary', ['reportId' => $reportId]),
        ];
    }
}
