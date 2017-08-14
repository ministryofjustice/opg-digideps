<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        'action',
        'action-more-info',
        'asset',
        'debt',
        'fee',
        'balance',
        'client',
        'contact',
        'debts',
        'decision',
        'visits-care',
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
        'documents'
    ];

    /**
     * List of reports.
     *
     * @Route("/reports/{type}", name="reports")
     * @Template()
     */
    public function indexAction(Request $request, $type)
    {
        $user = $this->getUserWithData(['user-clients', 'client', 'report', 'client-reports']);

        // NDR: redirect to ODR index
        if ($user->isOdrEnabled()) {
            return $this->redirectToRoute('odr_index');
        }

        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        $reports = $client ? $client->getReports() : [];
        arsort($reports);

        return [
            'client' => $client,
            'reports' => $reports,
            'lastSignedIn' => $request->getSession()->get('lastLoggedIn'),
            'filter' => 'propFinance', // extend with param when required
        ];
    }

    /**
     * Edit single report
     *
     * @Route("/reports/edit/{reportId}", name="report_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId);
        $client = $report->getClient();
        $editReportDatesForm = $this->createForm(new FormDir\Report\ReportType('report_edit'), $report, [
            'translation_domain' => 'report',
        ]);
        $returnLink = $this->getUser()->isDeputyPa() ?
            $this->generateClientProfileLink($report->getClient())
            : $this->generateUrl('reports', ['type' => $report->getType()]);

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
     * @Template()
     */
    public function createAction(Request $request, $clientId, $action = false)
    {
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'client-reports']);

        $existingReports = $this->getReportsIndexedById($client);

        if ($action == 'create' && ($firstReport = array_shift($existingReports)) && $firstReport instanceof EntityDir\Report\Report) {
            $report = $firstReport;
        } else {
            // new report
            $report = new EntityDir\Report\Report();
        }
        $report->setClient($client);

        $form = $this->createForm(new FormDir\Report\ReportType('report'), $report, [
                'translation_domain' => 'registration',
                'action' => $this->generateUrl('report_create', ['clientId' => $clientId]) //TODO useless ?
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $response = $this->getRestClient()->post('report', $form->getData());

            return $this->redirect($this->generateUrl('report_overview', ['reportId' => $response['report']]));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        // get all the groups (needed by EntityDir\Report\Status
        /** @var EntityDir\Report\Report $report */
        $report = $this->getReportIfNotSubmitted($reportId, ['status', 'notes', 'user', 'client', 'client-reports']);

        // Lay and PA users have different views.
        // PA overview is named "client profile" from the business side
        $template = $report->getHas106flag()
            ? 'AppBundle:Pa/ClientProfile:overview.html.twig'
            : 'AppBundle:Report/Report:overview.html.twig';

        return $this->render($template, [
            'report' => $report,
            'reportStatus' => $report->getStatus(),
        ]);
    }

    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, ['status']);

        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        // check status
        $status = $report->getStatus();
        if (!$report->isDue() || !$status->getIsReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }

        $user = $this->getUserWithData(['user-clients', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(new FormDir\Report\ReportDeclarationType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());

            // store PDF as a document
            $pdfBinaryContent = $this->getPdfBinaryContent($report->getId());
            $fileUploader = $this->get('file_uploader');
            $fileUploader->uploadFile(
                $report->getId(),
                $pdfBinaryContent,
                $report->createAttachmentName('DigiRep-%s_%s_%s.pdf'),
                true
            );

            // store report and get new YEAR report
            $newReportId = $this->getRestClient()->put('report/' . $report->getId() . '/submit', $report, ['submit']);
            $newReport = $this->getRestClient()->get('report/' . $newReportId['newReportId'], 'Report\\Report');

            //send confirmation email
            if ($user->isDeputyPa()) {
                $reportConfirmEmail = $this->getMailFactory()->createPaReportSubmissionConfirmationEmail($this->getUser(), $report, $newReport, $pdfBinaryContent);
                $this->getMailSender()->send($reportConfirmEmail, ['text', 'html'], 'secure-smtp');
            } else {
                $reportConfirmEmail = $this->getMailFactory()->createReportSubmissionConfirmationEmail($this->getUser(), $report, $newReport);
                $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);
            }

            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId' => $report->getId()]));
        }

        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     *
     * @Route("/report/{reportId}/submitted", name="report_submit_confirmation")
     * @Template()
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

        $form = $this->createForm('feedback_report', new ModelDir\FeedbackReport());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($form->getData());
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
     * @Template()
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
     * @Template()
     */
    public function reviewAction($reportId)
    {
        /** @var EntityDir\Report\Report $report */
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        // check status
        $status = $report->getStatus();

        if ($this->getUser()->isDeputyPa()) {
            $backLink = $this->generateClientProfileLink($report->getClient());
        } else {
            $backLink = $this->generateUrl('reports', ['type' => $report->getType()]);
        }

        return [
            'report' => $report,
            'reportStatus' => $status,
            'backLink' => $backLink
        ];
    }

    /**
     * @Route("/report/deputyreport-{reportId}.pdf", name="report_pdf")
     */
    public function pdfViewAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsAll);
        $pdfBinary = $this->getPdfBinaryContent($reportId);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $name = 'OPG102-' . $report->getClient()->getCaseNumber() . '-' . date_format($report->getEndDate(), 'Y') . '.pdf';

        $attachmentName = sprintf('DigiRep-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');
//        $response->headers->set('Content-length', strlen($->getSize()); // not easy to calculate binary size in bytes

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsAll);

        $html = $this->render('AppBundle:Report/Formatted:formatted_body.html.twig', [
                'report' => $report,
            ])->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }
}
