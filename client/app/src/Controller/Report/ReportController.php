<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Client;
use App\Entity\DeputyInterface;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Event\RegistrationSucceededEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\DisplayableException;
use App\Exception\ReportNotSubmittableException;
use App\Exception\ReportNotSubmittedException;
use App\Form\FeedbackReportType;
use App\Form\Report\ReportDeclarationType;
use App\Form\Report\ReportType;
use App\Model\FeedbackReport;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\Internal\SatisfactionApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Csv\TransactionsCsvGenerator;
use App\Service\File\Storage\S3Storage;
use App\Service\Redirector;
use App\Service\ReportSubmissionService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    /**
     * JMS groups used for report preview and PDF.
     */
    private static array $reportGroupsAll = [
        'account',
        'action-more-info',
        'action',
        'asset',
        'balance',
        'balance-state',
        'client',
        'client-benefits-check',
        'client-deputy',
        'contact',
        'debt',
        'debts',
        'decision',
        'debt-management',
        'documents',
        'expenses',
        'fee',
        'gifts',
        'lifestyle',
        'moneyShortCategoriesIn',
        'moneyShortCategoriesOut',
        'moneyTransactionsShortIn',
        'moneyTransactionsShortOut',
        'mental-capacity',
        'money-transfer',
        'prof-deputy-costs-estimate-how-charged',
        'prof-deputy-costs-estimate-more-info',
        'prof-deputy-costs-how-charged',
        'prof-deputy-costs-interim',
        'prof-deputy-costs-prev',
        'prof-deputy-estimate-costs',
        'prof-deputy-estimate-management-costs',
        'prof-deputy-other-costs',
        'prof-service-fees',
        'report',
        'report-documents',
        'report-prof-deputy-costs',
        'report-prof-deputy-costs-interim',
        'report-prof-deputy-costs-prev',
        'report-prof-deputy-costs-scco',
        'report-prof-deputy-fixed-cost',
        'report-prof-service-fees',
        'report-submitted-by',
        'status',
        'transaction',
        'transactionsIn',
        'transactionsOut',
        'unsubmitted-reports-count',
        'visits-care',
        'wish-to-provide-documentation',
    ];

    public function __construct(
        private readonly RestClient $restClient,
        private readonly ReportApi $reportApi,
        private readonly UserApi $userApi,
        private readonly ClientApi $clientApi,
        private readonly SatisfactionApi $satisfactionApi,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
        private readonly ObservableEventDispatcher $eventDispatcher,
        private readonly S3Storage $s3Storage,
    ) {
    }

    /**
     * Edit single report.
     *
     * @throws \Exception
     */
    #[Route(path: '/reports/edit/{reportId}', name: 'report_edit')]
    #[Template('@App/Report/Report/edit.html.twig')]
    public function editAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId);
        $client = $report->getClient();

        /** @var User $user */
        $user = $this->getUser();

        $editReportDatesForm = $this->formFactory->createNamed('report_edit', ReportType::class, $report, ['translation_domain' => 'report']);
        $returnLink = $user->isDeputyOrg()
            ? $this->clientApi->generateClientProfileLink($report->getClient())
            : $this->generateUrl('courtorders_for_deputy');

        $editReportDatesForm->handleRequest($request);
        if ($editReportDatesForm->isSubmitted() && $editReportDatesForm->isValid()) {
            $this->restClient->put('report/' . $reportId, $report, ['startEndDates']);

            return $this->redirect($returnLink);
        }

        return [
            'client' => $client,
            'report' => $report,
            'form' => $editReportDatesForm->createView(),
            'returnLink' => $returnLink,
        ];
    }

    /**
     * Create report
     * default action "create" will create only one report (used during registration steps to avoid duplicates when going back from the browser)
     * action "add" will instead add another report.
     */
    #[Route(path: '/report/{action}/{clientId}', name: 'report_create', requirements: ['action' => '(create|add)'], defaults: ['action' => 'create'])]
    #[Template('@App/Report/Report/create.html.twig')]
    public function createAction(Request $request, string $clientId): RedirectResponse|array
    {
        $client = $this->restClient->get('client/' . $clientId, 'Client', ['client', 'client-id', 'client-reports', 'report-id']);

        $existingReports = $this->reportApi->getReportsIndexedById($client);

        if (count($existingReports)) {
            throw $this->createAccessDeniedException('Client already has a report');
        }

        $report = new Report();
        $report->setClient($client);

        $form = $this->formFactory->createNamed(
            'report',
            ReportType::class,
            $report,
            [
                'translation_domain' => 'registration',
                'action' => $this->generateUrl('report_create', ['clientId' => $clientId]), // TODO useless ?
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->post('report', $form->getData());

            $user = $this->userApi->getUserWithData();
            $this->eventDispatcher->dispatch(new RegistrationSucceededEvent($user), RegistrationSucceededEvent::DEPUTY);

            return $this->redirect($this->generateUrl('homepage'));
        }

        return [
            'form' => $form->createView(),
            'clientId' => $clientId,
        ];
    }

    #[Route(path: '/report/{reportId}/overview', name: 'report_overview', requirements: ['reportId' => '\d+'])]
    #[Template('@App/Report/Report/overview.html.twig')]
    public function overviewAction(Redirector $redirector, int $reportId): RedirectResponse|Response
    {
        $user = $this->userApi->getUserWithData();

        // redirect if user has missing details or is on wrong page
        $route = $redirector->getCorrectRouteIfDifferent($user, 'report_overview');
        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $reportJmsGroup = ['status', 'balance', 'user', 'client', 'client-reports', 'balance-state'];

        // get all the groups (needed by EntityDir\Report\Status)
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, $reportJmsGroup);

        $client = $this->generateClient($user, "{$report->getClient()->getId()}");

        $activeReport = null;
        $template = '@App/Report/Report/overview.html.twig';

        if ($user->isDeputyOrg()) {
            $template = '@App/Org/ClientProfile/overview.html.twig';

            // if there is an unsubmitted report, put that report above the current (active) report
            // and mark the unsubmitted report as "incomplete"
            $unsubmittedReport = $client->getUnsubmittedReport();

            if (!is_null($unsubmittedReport)) {
                $activeReport = $report;

                $report = $this->reportApi->getReportIfNotSubmitted(
                    $unsubmittedReport->getId(),
                    $reportJmsGroup,
                );
            }
        }

        return $this->render($template, [
            'user' => $user,
            'client' => $client,
            'deputy' => $client->getDeputy(),
            'report' => $report,
            'activeReport' => $activeReport,
        ]);
    }

    #[Route(path: '/report/{reportId}/confirm-details', name: 'report_confirm_details')]
    #[Template('@App/Report/Report/confirm-details.html.twig')]
    public function confirmDetailsAction(int $reportId): array|RedirectResponse
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$reportGroupsAll);

        // check status
        $status = $report->getStatus();
        if (!$report->isDue() || !$status->getIsReadyToSubmit()) {
            $message = $this->translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators');
            throw new ReportNotSubmittableException($message);
        }

        $deputy = $report->getClient()->getDeputy();

        if (is_null($deputy)) {
            $deputy = $this->userApi->getUserWithData();
        }

        return [
            'report' => $report,
            'contactDetails' => $this->getAssociatedContactDetails($deputy, $report),
        ];
    }


    #[Route(path: '/report/{reportId}/declaration', name: 'report_declaration')]
    #[Template('@App/Report/Report/declaration.html.twig')]
    public function declarationAction(Request $request, int $reportId, ReportSubmissionService $reportSubmissionService): RedirectResponse|array
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$reportGroupsAll);

        // check status
        $status = $report->getStatus();
        if (!$report->isDue() || !$status->getIsReadyToSubmit()) {
            $message = $this->translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators');
            throw new ReportNotSubmittableException($message);
        }

        $form = $this->createForm(ReportDeclarationType::class, $report);
        $form->handleRequest($request);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $reportSubmissionService->generateReportDocuments($report);

            $this->reportApi->submit($report, $currentUser);

            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId' => $report->getId()]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     */
    #[Route(path: '/report/{reportId}/submitted', name: 'report_submit_confirmation')]
    #[Template('@App/Report/Report/submitConfirmation.html.twig')]
    public function submitConfirmationAction(Request $request, int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReport($reportId, ['status']);

        // check status
        if (!$report->getSubmitted()) {
            $message = $this->translator->trans('report.submissionExceptions.submitted', [], 'validators');
            throw new ReportNotSubmittedException($message);
        }

        $form = $this->createForm(FeedbackReportType::class, new FeedbackReport());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $satisfactionId = $this->satisfactionApi->createPostSubmissionFeedback($form->getData(), $report->getType(), $reportId);
            $postSubmissionUrl = $this->generateUrl('report_post_submission_user_research', ['reportId' => $reportId, 'satisfactionId' => $satisfactionId]);

            return $this->redirect($postSubmissionUrl);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'homePageName' => $this->getUser()->isLayDeputy() ? 'courtorders_for_deputy' : 'org_dashboard',
        ];
    }

    /**
     * Used for active and archived report.
     *
     * @throws \Exception
     */
    #[Route(path: '/report/{reportId}/review', name: 'report_review')]
    #[Template('@App/Report/Report/review.html.twig')]
    public function reviewAction(int $reportId): RedirectResponse|array
    {
        $report = $this->reportApi->getReport($reportId, self::$reportGroupsAll);

        // check status
        $status = $report->getStatus();

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isDeputyOrg()) {
            $backLink = $this->clientApi->generateClientProfileLink($report->getClient());
        } else {
            $backLink = $this->generateUrl('courtorders_for_deputy');
        }

        if (!$report->isSubmitted()) {
            // Redirect deputy to doc re-upload page if docs do not exist in S3
            $documentsNotInS3 = $this->checkIfDocumentsExistInS3($report);

            if (!empty($documentsNotInS3)) {
                return $this->redirectToRoute('report_documents_reupload', ['reportId' => $reportId]);
            }
        }

        return [
            'user' => $this->getUser(),
            'report' => $report,
            'reportStatus' => $status,
            'backLink' => $backLink,
            'feeTotals' => $report->getFeeTotals(),
        ];
    }

    private function checkIfDocumentsExistInS3(Report $report): array
    {
        // Retrieve document storage reference numbers and store in array
        $documentIds = [];
        foreach ($report->getDeputyDocuments() as $document) {
            $documentIds[] = $document->getId();
        }

        $documentStorageReferences = [];
        foreach ($documentIds as $documentId) {
            $documentStorageReferences[] = $this->restClient->get(
                sprintf('document/%s', $documentId),
                'Report\Document',
                ['document-storage-reference']
            )->getStorageReference();
        }

        // call Document Service and check if documents exist in the S3 bucket
        $documentsNotInS3 = [];

        // loop through references and check if they exist in S3, as soon as a file is not found in S3 redirect to re-uploads page
        foreach ($documentStorageReferences as $docStorageReference) {
            if (!$this->s3Storage->checkFileExistsInS3($docStorageReference)) {
                $documentsNotInS3[] = $docStorageReference;
            }
        }

        return $documentsNotInS3;
    }

    /**
     * Used for active and archived report.
     */
    #[Route(path: '/report/{reportId}/pdf-debug', name: 'report_pdf_debug')]
    public function pdfDebugAction(int $reportId): Response
    {
        if (!$this->getParameter('kernel.debug')) {
            throw new DisplayableException('Route only visited in debug mode');
        }
        $report = $this->reportApi->getReport($reportId, self::$reportGroupsAll);

        return $this->render('@App/Report/Formatted/formatted_standalone.html.twig', [
            'report' => $report,
            'showSummary' => true,
        ]);
    }

    #[Route(path: '/report/deputyreport-{reportId}.pdf', name: 'report_pdf')]
    public function pdfViewAction(int $reportId, ReportSubmissionService $reportSubmissionService): Response
    {
        $report = $this->reportApi->getReport($reportId, self::$reportGroupsAll);
        $pdfBinary = $reportSubmissionService->getPdfBinaryContent($report);

        if (false === $pdfBinary) {
            // unable to get the PDF for the report
            throw $this->createNotFoundException();
        }

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $submitDate = $report->getSubmitDate();

        /** @var \DateTime $endDate */
        $endDate = $report->getEndDate();

        $attachmentName = sprintf(
            'DigiRep-%s_%s_%s.pdf',
            $endDate->format('Y'),
            $submitDate instanceof \DateTime ? $submitDate->format('Y-m-d') : 'n-a-', // some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    /**
     * Generates Transactions CSV and returns as CSV file response.
     */
    #[Route(path: '/report/transactions-{reportId}.csv', name: 'report_transactions_csv')]
    public function transactionsCsvViewAction(int $reportId, TransactionsCsvGenerator $csvGenerator): Response
    {
        $report = $this->reportApi->getReport($reportId, self::$reportGroupsAll);

        // restrict access to only 102, 102-4 reports
        $reportType = $report->getType();
        if (!in_array($reportType, ['102', '102-4'])) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $csvContent = $csvGenerator->generateTransactionsCsv($report);

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');

        $submitDate = $report->getSubmitDate();

        /** @var \DateTime $endDate */
        $endDate = $report->getEndDate();

        $attachmentName = sprintf(
            'DigiRepTransactions-%s_%s_%s.csv',
            $endDate->format('Y'),
            $submitDate instanceof \DateTime ? $submitDate->format('Y-m-d') : 'n-a-', // some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getAssociatedContactDetails(DeputyInterface $deputy, Report $report): array
    {
        return [
            'client' => $this->getClientContactDetails($report),
            'deputy' => $this->getDeputyContactDetails($deputy, $report),
        ];
    }

    private function getClientContactDetails(Report $report): array
    {
        $client = $report->getClient();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        return [
            'name' => $client->getFullName() . ' (client)',
            'address' => $client->getAddressNotEmptyParts(),
            'phone' => ['main' => $client->getPhone()],
            'email' => $client->getEmail(),
            'editUrl' => $currentUser->isLayDeputy() ?
                $this->generateUrl('client_edit', ['clientId' => $client->getId(), 'from' => 'declaration']) :
                $this->generateUrl('org_client_edit', ['clientId' => $client->getId(), 'from' => 'declaration']),
        ];
    }

    private function getDeputyContactDetails(DeputyInterface $deputy, Report $report): array
    {
        if ($deputy instanceof User) {
            if ($deputy->isLayDeputy()) {
                $editUrl = $this->generateUrl('user_edit', ['from' => 'declaration', 'rid' => $report->getId()]);
            } else {
                $editUrl = $this->generateUrl('org_profile_edit', ['from' => 'declaration', 'rid' => $report->getId()]);
            }
        } else {
            $editUrl = '';
        }

        return [
            'name' => $deputy->getFullName() . ' (deputy)',
            'address' => $deputy->getAddressNotEmptyParts(),
            'phone' => [
                'main' => $deputy->getPhoneMain(),
                'alternative' => $deputy->getPhoneAlternative(),
            ],
            'email' => $deputy->getEmail(),
            'editUrl' => $editUrl,
        ];
    }
    /**
     * Due to some profs having many dozens of deputies attached to clients, we need to be conservative about generating
     * the list. It's needed for a permissions check on add client contact (logged-in user has to be associated).
     */
    private function generateClient(User $user, string $clientId): Client
    {
        $jms = $this->determineJmsGroups($user);

        /* Get client with all other JMS groups required */
        $client = $this->restClient->get("client/$clientId", 'Client', $jms);

        if ($user->isDeputyOrg()) {
            /*
            Separate call to get client Users as query taking too long for some profs with many deputies attached.
            We only need the user id for the add client contact permission check
             */
            $clientWithUsers = $this->restClient->get("client/$clientId", 'Client', ['user-id', 'client-users']);
            $client->setUsers($clientWithUsers->getUsers());
        }

        return $client;
    }

    /**
     * Method to return JMS groups required for overview page.
     */
    private function determineJmsGroups(User $user): array
    {
        $jms = [
            'client',
            'user',
            'client-reports',
            'report', // needed ?
            'client-clientcontacts',
            'clientcontact',
            'client-notes',
            'notes',
        ];

        if ($user->isLayDeputy()) {
            $jms[] = 'client-users';
        } elseif ($user->isDeputyOrg()) {
            $jms[] = 'client-deputy';
            $jms[] = 'deputy';
        }

        return $jms;
    }
}
