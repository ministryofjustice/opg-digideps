<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\SynchronisableInterface;
use App\Exception\ReportNotSubmittedException;
use App\Form\Admin\CloseReportConfirmType;
use App\Form\Admin\CloseReportType;
use App\Form\Admin\ManageActiveReportType;
use App\Form\Admin\ManageReportConfirmType;
use App\Form\Admin\ManageSubmittedReportType;
use App\Form\Admin\ReportChecklistType;
use App\Form\Admin\ReviewChecklistType;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\ParameterStoreService;
use App\Service\ReportSubmissionService;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/report/{id}/", requirements={"id":"\d+"})
 */
class ReportController extends AbstractController
{
    /**
     * JMS groups used for report preview and PDF
     * //TODO consider take/merge the value from ReportController::$reportGroupsAll.
     *
     * @var array
     */
    private static $reportGroupsAll = [
        'report',
        'client',
        'account',
        'expenses',
        'fee',
        'gifts',
        'prof-deputy-other-costs',
        'prof-deputy-costs-how-charged',
        'report-prof-deputy-costs',
        'report-prof-deputy-costs-prev', 'prof-deputy-costs-prev',
        'report-prof-deputy-costs-interim', 'prof-deputy-costs-interim',
        'report-prof-deputy-costs-scco',
        'report-prof-deputy-fixed-cost',
        'prof-deputy-estimate-costs',
        'prof-deputy-costs-estimate-how-charged',
        'prof-deputy-estimate-management-costs',
        'prof-deputy-costs-estimate-more-info',
        'action',
        'action-more-info',
        'asset',
        'debt',
        'debt-management',
        'fee',
        'balance',
        'contact',
        'debts',
        'decision',
        'visits-care',
        'lifestyle',
        'mental-capacity',
        'money-transfer',
        'transaction',
        'transactionsIn',
        'transactionsOut',
        'moneyShortCategoriesIn',
        'moneyShortCategoriesOut',
        'moneyTransactionsShortIn',
        'moneyTransactionsShortOut',
        'status',
        'report-submitted-by',
        'wish-to-provide-documentation',
        'report-documents',
        'balance-state',
        'documents',
        'report-prof-service-fees',
        'prof-service-fees',
        'client-deputy',
        'client-benefits-check',
        'client-benefits-check-state',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    private LoggerInterface $logger;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi,
        LoggerInterface $logger
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->logger = $logger;
    }

    /**
     * @Route("checklist", name="admin_report_checklist")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param string $id
     *
     * @Template("@App/Admin/Client/Report/checklist.html.twig")
     *
     * @return array|RedirectResponse|Response
     */
    public function checklistAction(Request $request, $id)
    {
        $report = $this->reportApi->getReport(
            intval($id),
            array_merge(
                self::$reportGroupsAll,
                [
                    'report-checklist', 'checklist-information', 'last-modified', 'user', 'previous-report-data', 'action', 'report-submitted-by', 'synchronisation',
                ]
            )
        );

        if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
            throw new ReportNotSubmittedException('Cannot lodge a checklist for an incomplete report');
        }

        $checklist = $report->getChecklist();
        $checklist = empty($checklist) ? new Checklist($report) : $checklist;

        /** @var Form $form */
        $form = $this->createForm(ReportChecklistType::class, $checklist, ['report' => $report]);
        $form->handleRequest($request);

        /** @var SubmitButton $buttonClicked */
        $buttonClicked = $form->getClickedButton();

        $reviewChecklist = $this->restClient->get('report/'.$report->getId().'/checklist', 'Report\\ReviewChecklist');
        /** @var Form $reviewForm */
        $reviewForm = $this->createForm(ReviewChecklistType::class, $reviewChecklist);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            /** @var SubmitButton $button */
            $button = $reviewForm->getClickedButton();
            if (ReviewChecklistType::SUBMIT_ACTION === $button->getName()) {
                $reviewChecklist->setIsSubmitted(true);
            }

            if (!empty($reviewChecklist->getId())) {
                $this->restClient->put('report/'.$report->getId().'/checklist', $reviewChecklist);
            } else {
                $this->restClient->post('report/'.$report->getId().'/checklist', $reviewChecklist);
            }

            if (ReviewChecklistType::SUBMIT_ACTION === $button->getName()) {
                return $this->redirect($this->generateUrl('admin_report_checklist_submitted', ['id' => $report->getId()]));
            } else {
                $this->addFlash('notice', 'Review checklist saved');

                return $this->redirect($this->generateUrl('admin_report_checklist', ['id' => $report->getId()]).'#anchor-fullReview-checklist');
            }
        }

        if ($buttonClicked instanceof SubmitButton) {
            $checklist->setButtonClicked($buttonClicked->getName());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($checklist->getId())) {
                $this->restClient->put('report/'.$report->getId().'/checked', $checklist, [
                    'report-checklist', 'checklist-information',
                ]);
            } else {
                $this->restClient->post('report/'.$report->getId().'/checked', $checklist, [
                    'report-checklist', 'checklist-information',
                ]);
            }

            /** @var Session $session */
            $session = $request->getSession();

            if (!$session->getFlashBag()->has('notice')) {
                // the duplicate notice is because the PDF view action doesn't actually refresh the page and therefore the original
                // 'saved' notice never gets rendered
                $this->addFlash('notice', 'Lodging checklist saved');
            }

            if ('saveFurtherInformation' == $buttonClicked->getName()) {
                return $this->redirect(
                    $this->generateUrl('admin_report_checklist', ['id' => $report->getId()]).'#furtherInformation'
                );
            } else {
                if ('submitAndContinue' == $buttonClicked->getName()) {
                    return $this->redirect($this->generateUrl('admin_report_checklist_submitted', ['id' => $report->getId()]));
                } else {
                    return $this->redirect($this->generateUrl('admin_report_checklist', ['id' => $report->getId()]).'#');
                }
            }
        }

        $costBreakdown = null;

        if (Report::PROF_DEPUTY_COSTS_TYPE_FIXED !== $report->getProfDeputyCostsEstimateHowCharged()) {
            $costBreakdown = $report->generateActualSubmittedEstimateCosts();
        }

        $syncStatus = null;

        if ($checklist->getSynchronisationStatus()) {
            $syncStatus = match ($checklist->getSynchronisationStatus()) {
                SynchronisableInterface::SYNC_STATUS_QUEUED, SynchronisableInterface::SYNC_STATUS_IN_PROGRESS => 'Pending',
                SynchronisableInterface::SYNC_STATUS_PERMANENT_ERROR, SynchronisableInterface::SYNC_STATUS_TEMPORARY_ERROR => 'Failed',
                SynchronisableInterface::SYNC_STATUS_SUCCESS => 'Sent to Sirius',
            };
        }

        return [
            'report' => $report,
            'submittedEstimateCosts' => $costBreakdown,
            'form' => $form->createView(),
            'reviewForm' => $reviewForm->createView(),
            'checklist' => $checklist,
            'reviewChecklist' => $reviewChecklist,
            'previousReportData' => $report->getPreviousReportData(),
            'reportOrNdr' => $report instanceof Ndr ? 'ndr' : 'report',
            'syncStatus' => $syncStatus,
        ];
    }

    /**
     * @Route("checklist-submitted", name="admin_report_checklist_submitted")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return array
     *
     * @Template("@App/Admin/Client/Report/checklistSubmitted.html.twig")
     */
    public function checklistSubmittedAction(int $id, ParameterStoreService $parameterStore)
    {
        $report = $this->reportApi->getReport(intval($id), ['report-checklist']);
        $syncFeatureIsEnabled = false;

        if ('1' === $parameterStore->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC)) {
            $syncFeatureIsEnabled = true;
            $this->queueChecklistForSyncing($report);
        }

        return [
            'report' => $report,
            'syncFeatureIsEnabled' => $syncFeatureIsEnabled,
        ];
    }

    protected function queueChecklistForSyncing(Report $report): void
    {
        $report->getChecklist()->setSynchronisationStatus(Checklist::SYNC_STATUS_QUEUED);
        $this->restClient->put('report/'.$report->getId().'/checked', $report->getChecklist(), ['synchronisation']);
    }

    /**
     * Generate and return Checklist as Response object.
     *
     * @Route("checklist.pdf", name="admin_checklist_pdf")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function checklistPDFViewAction(int $id, ReportSubmissionService $reportSubmissionService)
    {
        $report = $this->reportApi->getReport(intval($id), array_merge(self::$reportGroupsAll, ['report-checklist', 'checklist-information', 'user']));

        $pdfBinary = $reportSubmissionService->getChecklistPdfBinaryContent($report);
        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        if (is_null($report->getEndDate())) {
            throw $this->createNotFoundException();
        }

        $attachmentName = sprintf(
            'DigiChecklist-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() instanceof \DateTime ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', // some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="'.$attachmentName.'"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    /**
     * Generate and upload the.
     *
     * @Route("regenerate-pdf", name="admin_regenerate_pdf", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/ReportSubmission/regenerate-pdf.html.twig")
     *
     * @return array
     */
    public function regeneratePDF(int $id, ReportSubmissionService $reportSubmissionService)
    {
        $report = $this->reportApi->getReport($id, self::$reportGroupsAll);
        $reportSubmissionService->generateReportPdf($report, true);

        return [
            'reportId' => $id,
        ];
    }

    /**
     * @Route("manage", name="admin_report_manage")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @param string $id
     *
     * @Template("@App/Admin/Client/Report/manage.html.twig")
     *
     * @return array|Response|RedirectResponse
     *
     * @throws \Exception
     */
    public function manageAction(Request $request, $id)
    {
        $report = $this->reportApi->getReport(intval($id), ['report-checklist', 'action']);

        $formClass = ($report->isSubmitted()) ? ManageSubmittedReportType::class : ManageActiveReportType::class;
        $form = $this->createForm($formClass, $report);

        if (is_array($request->get('data'))) {
            $this->prepopulateWithPreviousChoices($request->get('data'), $form);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->setChoicesInSession($request, $form, $report);

            return $this->redirect($this->generateUrl('admin_report_manage_confirm', ['id' => $report->getId()]));
        }

        $closeReportForm = null;
        if ($report->isUnsubmitted()) {
            $closeReportForm = $this->createForm(CloseReportType::class);
            $closeReportForm->handleRequest($request);

            if ($closeReportForm->isSubmitted() && $closeReportForm->isValid()) {
                return $this->redirect($this->generateUrl('admin_report_manage_close_report_confirm', ['id' => $report->getId()]));
            }
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'closeForm' => $closeReportForm ? $closeReportForm->createView() : null,
        ];
    }

    /**
     * @throws \Exception
     */
    private function prepopulateWithPreviousChoices(array $dataFromUrl, FormInterface $form): void
    {
        foreach (['type', 'dueDateChoice'] as $field) {
            $form->has($field) && $form[$field]->setData($dataFromUrl[$field]);
        }

        foreach (['dueDateCustom', 'startDate', 'endDate'] as $field) {
            $form->has($field) && array_key_exists($field, $dataFromUrl) && $form[$field]->setData(new \DateTime($dataFromUrl[$field]));
        }

        if ($form->has('unsubmittedSection') && isset($dataFromUrl['unsubmittedSectionsList'])) {
            foreach ($form['unsubmittedSection']->getData() as $index => $section) {
                $unsubmitted = explode(',', $dataFromUrl['unsubmittedSectionsList']);
                if (in_array($section->getId(), $unsubmitted)) {
                    $form['unsubmittedSection']->getData()[$index]->setPresent(true);
                }
            }

            $form['unsubmittedSection']->setData($form['unsubmittedSection']->getData());
        }
    }

    /**
     * @throws \Exception
     */
    private function setChoicesInSession(Request $request, FormInterface $form, Report $report): void
    {
        $customDueDate = $form['dueDateCustom']->getData();
        $startDate = isset($form['startDate']) ? $form['startDate']->getData()->format('Y-m-d') : null;
        $endDate = isset($form['endDate']) ? $form['endDate']->getData()->format('Y-m-d') : null;

        $request->getSession()->set('report-management-changes', [
            'type' => $form['type']->getData(),
            'dueDate' => $this->determineNewDueDateFromForm($report, $form)->format('Y-m-d'),
            'dueDateChoice' => $form['dueDateChoice']->getData(),
            'dueDateCustom' => $customDueDate instanceof \DateTime ? $customDueDate->format('Y-m-d') : null,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'unsubmittedSectionsList' => implode(',', $report->getUnsubmittedSectionsIds()),
        ]);
    }

    /**
     * @return \DateTime|null
     *
     * @throws \Exception
     */
    private function determineNewDueDateFromForm(Report $report, FormInterface $form)
    {
        $newDueDate = $report->getDueDate();

        if (preg_match('/^\d+$/', $form['dueDateChoice']->getData())) {
            $newDueDate = new \DateTime();
            $newDueDate->modify("+{$form['dueDateChoice']->getData()} weeks");
        } elseif ('custom' == $form['dueDateChoice']->getData() && $form['dueDateCustom']->getData() instanceof \DateTime) {
            $newDueDate = $form['dueDateCustom']->getData();
        }

        return $newDueDate;
    }

    /**
     * @Route("manage-confirm", name="admin_report_manage_confirm")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @return array|Response|RedirectResponse
     *
     * @throws \Exception
     *
     * @Template("@App/Admin/Client/Report/manageConfirm.html.twig")
     */
    public function manageConfirmAction(Request $request, $id)
    {
        $report = $this->reportApi->getReport(intval($id), ['report-checklist', 'action']);

        $sessionData = $request->getSession()->get('report-management-changes');
        if (null === $sessionData || !$this->sufficientDataInSession($sessionData)) {
            return $this->redirect($this->generateUrl('admin_report_manage', ['id' => $report->getId()]));
        }

        $form = $this->createForm(ManageReportConfirmType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('confirm') && 'no' === $form['confirm']->getData()) {
                // User decided not to update.
                return $this->redirect($this->generateUrl('admin_client_details', ['id' => $report->getClient()->getId()]));
            }

            $this->populateReportFromSession($report, $sessionData);
            $this->restClient->put('report/'.$report->getId(), $report, ['report_type', 'report_due_date']);

            if ($form->has('confirm') && 'yes' === $form['confirm']->getData() && $report->isSubmitted()) {
                $this->reportApi->unsubmit(
                    $report,
                    $this->getUser(),
                    AuditEvents::TRIGGER_UNSUBMIT_REPORT
                );
                $this->upsertChecklistInformation($report);
                $this->addFlash('notice', 'Report marked as incomplete');
            }

            $request->getSession()->remove('report-management-changes');

            return $this->redirect($this->generateUrl('admin_client_details', ['id' => $report->getClient()->getId()]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'submitted' => $sessionData,
        ];
    }

    private function sufficientDataInSession(array $sessionData): bool
    {
        return
            array_key_exists('type', $sessionData)
            && array_key_exists('dueDateChoice', $sessionData)
            && array_key_exists('dueDateCustom', $sessionData)
            && array_key_exists('startDate', $sessionData)
            && array_key_exists('endDate', $sessionData)
            && array_key_exists('unsubmittedSectionsList', $sessionData);
    }

    /**
     * @throws \Exception
     */
    private function populateReportFromSession(Report $report, array $sessionData): void
    {
        foreach (['type', 'unsubmittedSectionsList'] as $field) {
            if (isset($sessionData[$field])) {
                $setter = sprintf('set%s', ucfirst($field));
                $report->{$setter}($sessionData[$field]);
            }
        }

        foreach (['dueDate', 'startDate', 'endDate'] as $field) {
            if (isset($sessionData[$field])) {
                $setter = sprintf('set%s', ucfirst($field));
                $report->{$setter}(new \DateTime($sessionData[$field]));
            }
        }
    }

    /**
     * @Route("manage-close-report-confirm", name="admin_report_manage_close_report_confirm")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @return array|RedirectResponse
     *
     * @Template("@App/Admin/Client/Report/manageCloseReportConfirm.html.twig")
     *
     * @throws \Exception
     */
    public function manageCloseReportConfirmAction(Request $request, $id)
    {
        $form = $this->createForm(CloseReportConfirmType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $this->reportApi->getReport(intval($id));
            $report->setSubmitted(true);
            $report->setUnSubmitDate(null);
            $this->restClient->put('report/'.$id, $report, ['submitted', 'unsubmit_date']);

            return $this->redirect($this->generateUrl('admin_client_details', ['id' => $report->getClient()->getId()]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function upsertChecklistInformation(Report $report): void
    {
        $content = $this
            ->render('@App/Admin/Client/Report/Formatted/unsubmit_information.html.twig', ['report' => $report])
            ->getContent();

        $checklist = $report->getChecklist();
        $checklist = empty($checklist) ? new Checklist($report) : $checklist;
        $checklist->setFurtherInformationReceived($content);

        $httpMethod = empty($checklist->getId()) ? 'post' : 'put';
        $this->restClient->{$httpMethod}('report/'.$report->getId().'/checked', $checklist, [
            'report-checklist', 'checklist-information',
        ]);
    }
}
