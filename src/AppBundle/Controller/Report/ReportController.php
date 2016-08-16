<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    private static $reportGroupsForValidation = [
        'account',
        'action',
        'asset',
        'debt',
        'balance',
        'client',
        'contact',
        'debts',
        'decision',
        'safeguarding',
        'mental-capacity',
        'money-transfer',
        'transaction',
    ];

    /**
     * List of reports.
     *
     * @Route("/reports/{cot}/{reportId}", name="reports", defaults={"reportId" = ""})
     * @Template()
     */
    public function indexAction(Request $request, $cot, $reportId = null)
    {
        $user = $this->getUserWithData(['user', 'client', 'report']);
        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        $reports = $client ? $client->getReports() : [];
        $reports = array_filter($reports, function ($r) use ($cot) {
            return $r->getCourtOrderTypeId() == $cot;
        });
        arsort($reports);

        $report = new EntityDir\Report\Report();
        $report->setClient($client);

        // edit report dates
        if ($reportId) {
            $report = $this->getReport($reportId);
            $editReportDatesForm = $this->createForm(new FormDir\Report\ReportType('report_edit'), $report, [
                'translation_domain' => 'report-edit-dates',
            ]);
            $editReportDatesForm->handleRequest($request);
            if ($editReportDatesForm->isValid()) {
                $this->getRestClient()->put('report/'.$reportId, $report, ['startEndDates']);

                return $this->redirect($this->generateUrl('reports', ['cot' => $report->getCourtOrderTypeId()]));
            }
        }

        $newReportNotification = null;
        foreach ($reports as $report) {
            if ($report->getReportSeen() === false) {
                $newReportNotification = $this->get('translator')->trans('newReportNotification', [], 'client');

                $reportObj = $this->getReport($report->getId(), ['transactions']);
                //update report to say message has been seen
                $reportObj->setReportSeen(true);
                $this->getRestClient()->put('report/'.$report->getId(), $reportObj);
            }
        }

        return [
            'client' => $client,
            'report' => $report,
            'reports' => $reports,
            'reportId' => $reportId,
            'editReportDatesForm' => ($reportId) ? $editReportDatesForm->createView() : null,
            'lastSignedIn' => $request->getSession()->get('lastLoggedIn'),
            'newReportNotification' => $newReportNotification,
            'filter' => 'propFinance', // extend with param when required
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
        $client = $this->getRestClient()->get('client/'.$clientId, 'Client', ['client']);

        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();

        $existingReports = $this->getReportsIndexedById($client);

        if ($action == 'create' && ($firstReport = array_shift($existingReports)) && $firstReport instanceof EntityDir\Report\Report) {
            $report = $firstReport;
        } else {
            // new report
            $report = new EntityDir\Report\Report();

            //if client has property & affairs and health & welfare then give them property & affairs
            //else give them health and welfare
            if (count($allowedCourtOrderTypes) > 1) {
                $report->setCourtOrderTypeId(EntityDir\Report\Report::PROPERTY_AND_AFFAIRS);
            } else {
                $report->setCourtOrderTypeId($allowedCourtOrderTypes[0]);
            }
        }
        $report->setClient($client);

        $form = $this->createForm(new FormDir\Report\ReportType(), $report,
                                  ['action' => $this->generateUrl('report_create', ['clientId' => $clientId])]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $response = $this->getRestClient()->post('report', $form->getData());

            return $this->redirect($this->generateUrl('report_overview', ['reportId' => $response['report']]));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template("AppBundle:Report/Overview:overview.html.twig")
     */
    public function overviewAction($reportId)
    {
        // get all the groups (needed by ReportStatusService
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$reportGroupsForValidation);
        $reportStatusService = new ReportStatusService($report);

        return [
            'report' => $report,
            'reportStatus' => $reportStatusService,
        ];
    }

    /**
     * @Route("/report/{reportId}/add_further_information/{action}", 
     *  name="report_add_further_info", 
     *  defaults={"action": "view"}, 
     *  requirements={"action": "(view|edit)"}
     * )
     * @Template()
     */
    public function furtherInformationAction(Request $request, $reportId, $action = 'view')
    {
        /** @var \AppBundle\Entity\Report $report */
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        $reportStatusService = new ReportStatusService($report);
        if (!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }

        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(new FormDir\Report\ReportFurtherInfoType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // add furher info
            $this->getRestClient()->put('report/'.$report->getId(), $report, ['furtherInformation']);

            // next or save: redirect to report declration
            if ($form->get('saveAndContinue')->isClicked()) {
                return $this->redirect($this->generateUrl('report_declaration', ['reportId' => $reportId]));
            }
        }

        if (!$report->getFurtherInformation()) {
            $action = 'edit';
        }

        return [
            'action' => $action,
            'report' => $report,
            'reportStatus' => $reportStatusService,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        $reportStatusService = new ReportStatusService($report);
        if (!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }

        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(new FormDir\Report\ReportDeclarationType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $newReportId = $this->getRestClient()->put('report/'.$report->getId().'/submit', $report, ['submit']);

            $pdfBinaryContent = $this->getPdfBinaryContent($report->getId());
            $reportEmail = $this->getMailFactory()->createReportEmail($this->getUser(), $report, $pdfBinaryContent);
            $this->getMailSender()->send($reportEmail, ['html'], 'secure-smtp');

            $newReport = $this->getRestClient()->get('report/'.$newReportId['newReportId'], 'Report\\Report');

            //send confirmation email
            $reportConfirmEmail = $this->getMailFactory()->createReportSubmissionConfirmationEmail($this->getUser(), $report, $newReport);
            $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);

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
    public function submitConfirmationAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        if (!$report->getSubmitted()) {
            throw new \RuntimeException($translator->trans('submissionExceptions.submitted', [], 'validators'));
        }

        $form = $this->createForm('feedback_report', new ModelDir\FeedbackReport());
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($form->getData());
            $this->getMailSender()->send($feedbackEmail, ['html']);

            return $this->redirect($this->generateUrl('report_submit_feedback', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'homePageHeaderLink' => $this->generateUrl('client_show'),
        ];
    }

    /**
     * @Route("/report/{reportId}/submit_feedback", name="report_submit_feedback")
     * @Template()
     */
    public function submitFeedbackAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        if (!$report->getSubmitted()) {
            throw new \RuntimeException($translator->trans('submissionExceptions.submitted', [], 'validators'));
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
        /** @var \AppBundle\Entity\Report $report */
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        // check status
        $reportStatusService = new ReportStatusService($report);

        return [
            'report' => $report,
            'deputy' => $this->getUser(),
            'reportStatus' => $reportStatusService,
        ];
    }

    /**
     * @Route("/report/deputyreport-{reportId}.pdf", name="report_pdf")
     */
    public function pdfViewAction($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);
        $pdfBinary = $this->getPdfBinaryContent($reportId);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $name = 'OPG102-'.$report->getClient()->getCaseNumber().'-'.date_format($report->getEndDate(), 'Y').'.pdf';

        $attachmentName = sprintf('DigiRep-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="'.$attachmentName.'"');
//        $response->headers->set('Content-length', strlen($->getSize()); // not easy to calculate binary size in bytes

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($reportId)
    {
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        $html = $this->render('AppBundle:Report:formatted_body.html.twig', array(
                'report' => $report,
            ))->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }
}
