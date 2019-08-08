<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;

use AppBundle\Service\ReportSubmissionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    /**
     * JMS groups used for report preview and PDF
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
        'prof-deputy-costs-estimate-how-charged',
        'prof-deputy-estimate-costs',
        'prof-deputy-costs-estimate-more-info',
        'prof-deputy-estimate-management-costs',
        'action',
        'action-more-info',
        'asset',
        'debt',
        'debt-management',
        'fee',
        'balance',
        'client',
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
        'client-named-deputy',
        'unsubmitted-reports-count'
    ];

    /**
     * List of reports.
     *
     * @Route("/lay", name="lay_home")
     * //TODO we should add Security("has_role('ROLE_LAY_DEPUTY')") here, but not sure as not clear what "getCorrectRouteIfDifferent" does
     * @Template("AppBundle:Report/Report:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        // not ideal to specify both user-client and client-users, but can't fix this differently with DDPB-1711. Consider a separate call to get
        // due to the way
        $user = $this->getUserWithData(['user-clients', 'client', 'client-reports', 'report', 'status']);

        // redirect if user has missing details or is on wrong page
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'lay_home')) {
            return $this->redirectToRoute($route);
        }

        $clients = $user->getClients();
        if (empty($clients)) {
            throw new \Exception('Client not added');
        }
        $client = array_shift($clients);

        //refresh client adding codeputes (another API call to avoid recursion with users)
        $clientWithCoDeputies = $this->getRestClient()->get('client/' . $client->getId(), 'Client', ['client', 'client-users', 'user']);
        $coDeputies = $clientWithCoDeputies->getCoDeputies();

        return [
            'client' => $client,
            'coDeputies' => $coDeputies,
            'lastSignedIn' => $request->getSession()->get('lastLoggedIn')
        ];
    }

    /**
     * Edit single report
     *
     * @Route("/reports/edit/{reportId}", name="report_edit")
     * @Template("AppBundle:Report/Report:edit.html.twig")
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId);
        $client = $report->getClient();

        $editReportDatesForm = $this->get('form.factory')->createNamed('report_edit', FormDir\Report\ReportType::class, $report, [ 'translation_domain' => 'report']);
        $returnLink = $this->getUser()->isDeputyOrg()
            ? $this->generateClientProfileLink($report->getClient())
            : $this->generateUrl('lay_home');

        $editReportDatesForm->handleRequest($request);
        if ($editReportDatesForm->isValid()) {
            $this->getRestClient()->put('report/' . $reportId, $report, ['startEndDates']);

            return $this->redirect($returnLink);
        }

        return [
            'client' => $client,
            'report' => $report,
            'form' =>  $editReportDatesForm->createView(),
            'returnLink' => $returnLink
        ];
    }

    /**
     * Create report
     * default action "create" will create only one report (used during registration steps to avoid duplicates when going back from the browser)
     * action "add" will instead add another report.
     *
     *
     * @Route("/report/{action}/{clientId}", name="report_create",
     *   defaults={ "action" = "create"},
     *   requirements={ "action" = "(create|add)"}
     * )
     * @Template("AppBundle:Report/Report:create.html.twig")
     */
    public function createAction(Request $request, $clientId, $action = false)
    {
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'client-reports', 'report-id']);

        $existingReports = $this->getReportsIndexedById($client);

        if ($action == 'create' && ($firstReport = array_shift($existingReports)) && $firstReport instanceof EntityDir\Report\Report) {
            $report = $firstReport;
        } else {
            // new report
            $report = new EntityDir\Report\Report();
        }
        $report->setClient($client);
        $form = $this->get('form.factory')->createNamed(
            'report',
            FormDir\Report\ReportType::class, $report, [
                'translation_domain' => 'registration',
                'action'             => $this->generateUrl('report_create', ['clientId' => $clientId]) //TODO useless ?
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->post('report', $form->getData());
            return $this->redirect($this->generateUrl('homepage'));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template("AppBundle:Report/Report:overview.html.twig")
     */
    public function overviewAction(Request $request, $reportId)
    {
        $reportJmsGroup = ['status', 'balance', 'user', 'client', 'client-reports', 'balance-state'];
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'report_overview')) {
            return $this->redirectToRoute($route);
        }

        // get all the groups (needed by EntityDir\Report\Status
        /** @var EntityDir\Report\Report $report */
        $clientId = $this->getReportIfNotSubmitted($reportId, $reportJmsGroup)->getClient()->getId();

        /** @var $client EntityDir\Client */
        $client = $this->generateClient($user, $clientId);

        $activeReportId = null;
        if ($this->getUser()->isDeputyOrg()) {
            // PR and PROF: unsubmitted at the top (if exists), active below (
            $template = 'AppBundle:Org/ClientProfile:overview.html.twig';
            // if there is an unsubmitted report, swap them, so linkswill both show the unsubmitted first
            if ($client->getUnsubmittedReport()) {
                //alternative: redirect (but more API calls overall)
                $reportId = $client->getUnsubmittedReport()->getId();
                $activeReportId = $client->getActiveReport()->getId();
            }
        } else { // Lay. keep the report Id
            $template = 'AppBundle:Report/Report:overview.html.twig';
        }

        $report = $this->getReportIfNotSubmitted($reportId, $reportJmsGroup);
        $activeReport = $activeReportId ? $this->getReportIfNotSubmitted($activeReportId, $reportJmsGroup) : null;

        return $this->render($template, [
            'user' => $user,
            'client' => $client,
            'report' => $report,
            'activeReport' => $activeReport,
        ]);
    }

    /**
     * Due to some profs having many dozens of deputies attached to clients, we need to be conservative about generating
     * the list. Its needed for a permissions check on add client contact (logged in user has to be associated)
     *
     * @param EntityDir\User $user
     * @param $clientId
     * @return mixed
     */
    private function generateClient(EntityDir\User $user, $clientId)
    {
        $jms = $this->determineJmsGroups($user);

        /* Get client with all other JMS groups required */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', $jms);

        if ($user->isDeputyOrg()) {
            /*
            Separate call to get client Users as query taking too long for some profs with many deputies attached.
            We only need the user id for the add client contact permission check
             */
            $clientWithUsers = $this->getRestClient()->get('client/' . $clientId, 'Client', ['user-id', 'client-users']);
            $client->setUsers($clientWithUsers->getUsers());
        }

        return $client;
    }

    /**
     * Method to return JMS groups required for overview page.
     *
     * @param EntityDir\User $user
     * @return array
     */
    private function determineJmsGroups(EntityDir\User $user)
    {
        $jms = [
            'client',
            'user',
            'client-reports',
            'report', //needed ?
            'client-clientcontacts',
            'clientcontact',
            'client-notes',
            'notes',
        ];

        if ($user->isLayDeputy()) {
            $jms[] = 'client-users';
        }

        return $jms;
    }

    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template("AppBundle:Report/Report:declaration.html.twig")
     */
    public function declarationAction(Request $request, $reportId)
    {
        $reportSubmissionService = $this->get('report_submission_service'); /* @var $reportSubmissionService ReportSubmissionService */

        $report = $this->getReportIfNotSubmitted($reportId, self::$reportGroupsAll);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        // check status
        $status = $report->getStatus();
        if (!$report->isDue() || !$status->getIsReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }

        $user = $this->getUserWithData();

        $form = $this->createForm(FormDir\Report\ReportDeclarationType::class, $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $reportSubmissionService->generateReportDocuments($report);
            $reportSubmissionService->submit($report, $this->getUser());

            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId' => $report->getId()]));
        }

        return [
            'report' => $report,
            'client' => $report->getClient(),
            'contactDetails' => $this->getAssociatedContactDetails($user, $report),
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     *
     * @Route("/report/{reportId}/submitted", name="report_submit_confirmation")
     * @Template("AppBundle:Report/Report:submitConfirmation.html.twig")
     */
    public function submitConfirmationAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, ['status']);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        // check status
        if (!$report->getSubmitted()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.submitted', [], 'validators'));
        }

        $form = $this->createForm(FormDir\FeedbackReportType::class, new ModelDir\FeedbackReport());

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Store in database
            $this->getRestClient()->post('satisfaction', [
                'score' => $form->get('satisfactionLevel')->getData(),
                'reportType' => $report->getType()
            ]);

            // Send notification email
            $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($form->getData(), $this->getUser());
            $this->getMailSender()->send($feedbackEmail, ['html']);

            return $this->redirect($this->generateUrl('report_submit_feedback', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/submit_feedback", name="report_submit_feedback")
     * @Template("AppBundle:Report/Report:submitFeedback.html.twig")
     */
    public function submitFeedbackAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        // check status
        if (!$report->getSubmitted()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.submitted', [], 'validators'));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * Used for active and archived report.
     *
     * @Route("/report/{reportId}/review", name="report_review")
     * @Template("AppBundle:Report/Report:review.html.twig")
     */
    public function reviewAction($reportId)
    {
        /** @var EntityDir\Report\Report $report */
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        // check status
        $status = $report->getStatus();

        if ($this->getUser()->isDeputyOrg()) {
            $backLink = $this->generateClientProfileLink($report->getClient());
        } else {
            $backLink = $this->generateUrl('lay_home');
        }

        return [
            'user' => $this->getUser(),
            'report' => $report,
            'reportStatus' => $status,
            'backLink' => $backLink,
            'feeTotals' => $report->getFeeTotals()
        ];
    }

    /**
     * Used for active and archived report.
     *
     * @Route("/report/{reportId}/pdf-debug")
     */
    public function pdfDebugAction($reportId)
    {
        if (!$this->getParameter('kernel.debug')) {
            throw new DisplayableException('Route only visite in debug mode');
        }
        /** @var EntityDir\Report\Report $report */
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        return $this->render('AppBundle:Report/Formatted:formatted_standalone.html.twig', [
            'report' => $report,
            'showSummary' => true
        ]);
    }

    /**
     * @Route("/report/deputyreport-{reportId}.pdf", name="report_pdf")
     */
    public function pdfViewAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsAll);
        $pdfBinary = $this->get('report_submission_service')->getPdfBinaryContent($report);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiRep-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    /**
     * Generates Transactions CSV and returns as CSV file response
     *
     * @Route("/report/transactions-{reportId}.csv", name="report_transactions_csv")
     */
    public function transactionsCsvViewAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        // restrict access to only 102, 102-4 reports
        $reportType = $report->getType();
        if (!in_array($reportType, ['102', '102-4'])) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $csvContent = $this->get('csv_generator_service')->generateTransactionsCsv($report);

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');

        $attachmentName = sprintf('DigiRepTransactions-%s_%s_%s.csv',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    /**
     * @param User $user
     * @param Report $report
     * @return array
     */
    private function getAssociatedContactDetails(User $user, Report $report)
    {
        return [
            $this->getClientContactDetails($user, $report),
            $this->getDeputyContactDetails($user, $report)
        ];
    }

    /**
     * @param User $user
     * @param Report $report
     * @return array
     */
    private function getClientContactDetails(User $user, Report $report)
    {
        $client = $report->getClient();

        return [
            'name' => $client->getFullName() . ' (client)',
            'address' => $client->getAddressNotEmptyParts(),
            'phone' => ['main' => $client->getPhone()],
            'email' => $client->getEmail(),
            'editUrl' => $user->isLayDeputy() ?
                $this->generateUrl('client_edit', ['from' => 'declaration']) :
                $this->generateUrl('org_client_edit', ['clientId' => $client->getId(), 'from' => 'declaration'])
        ];
    }

    /**
     * @param User $user
     * @param Report $report
     * @return array
     */
    private function getDeputyContactDetails(User $user, Report $report)
    {
        return [
            'name' => $user->getFullName() . ' (deputy)',
            'address' => $user->getAddressNotEmptyParts(),
            'phone' => [
                'main' => $user->getPhoneMain(),
                'alternative' => $user->getPhoneAlternative()
            ],
            'email' => $user->getEmail(),
            'editUrl' => $user->isLayDeputy() ?
                $this->generateUrl('user_edit', ['from' => 'declaration', 'rid' => $report->getId()]) :
                $this->generateUrl('org_profile_edit', ['from' => 'declaration', 'rid' => $report->getId()])
        ];
    }
}
