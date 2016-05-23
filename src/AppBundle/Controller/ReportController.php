<?php

namespace AppBundle\Controller;

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
        'basic',  'accounts', 'client',
        'transactions', 'transactionsIn', 'transactionsOut',
        'asset', 'contacts', 'decisions', 'action', 'transfers',
        'MentalCapacity', //update other groups to this format
    ];

    /**
     * @Route("/reports/{cot}/{reportId}", name="reports", defaults={"reportId" = ""})
     * @Template()
     */
    public function indexAction($cot, $reportId = null)
    {
        $restClient = $this->get('restClient');

        $clients = $this->getUser()->getClients();
        $request = $this->getRequest();

        $client = !empty($clients) ? $clients[0] : null;

        $reports = $client ? $this->getReportsIndexedById($client, ['basic']) : [];
        $reports = array_filter($reports, function ($r) use ($cot) {
            return $r->getCourtOrderTypeId() == $cot;
        });
        arsort($reports);

        $report = new EntityDir\Report();
        $report->setClient($client);

        // edit report dates
        if ($reportId) {
            $report = $this->getReport($reportId, ['transactions', 'basic']);
            $editReportDatesForm = $this->createForm(new FormDir\ReportType('report_edit'), $report, [
                'translation_domain' => 'report-edit-dates',
            ]);
            $editReportDatesForm->handleRequest($request);
            if ($editReportDatesForm->isValid()) {
                $restClient->put('report/'.$reportId, $report, [
                     'deserialise_group' => 'startEndDates',
                ]);

                return $this->redirect($this->generateUrl('reports', ['cot' => $report->getCourtOrderTypeId()]));
            }
        }

        $newReportNotification = null;
        foreach ($reports as $report) {
            if ($report->getReportSeen() === false) {
                $newReportNotification = $this->get('translator')->trans('newReportNotification', [], 'client');

                $reportObj = $this->getReport($report->getId(), ['transactions', 'basic']);
              //update report to say message has been seen
              $reportObj->setReportSeen(true);

                $restClient->put('report/'.$report->getId(), $reportObj);
            }
        }

        return [
            'client' => $client,
            'report' => $report,
            'reports' => $reports,
            'reportId' => $reportId,
            'editReportDatesForm' => ($reportId) ? $editReportDatesForm->createView() : null,
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
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
    public function createAction($clientId, $action = false)
    {
        $request = $this->getRequest();
        $restClient = $this->get('restClient');

        $client = $this->getRestClient()->get('client/'.$clientId, 'Client', ['query' => ['groups' => ['basic']]]);

        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();

        $existingReports = $this->getReportsIndexedById($client, ['basic']);

        if ($action == 'create' && ($firstReport = array_shift($existingReports)) && $firstReport instanceof EntityDir\Report) {
            $report = $firstReport;
        } else {
            // new report
            $report = new EntityDir\Report();

            //if client has property & affairs and health & welfare then give them property & affairs
            //else give them health and welfare
            if (count($allowedCourtOrderTypes) > 1) {
                $report->setCourtOrderTypeId(EntityDir\Report::PROPERTY_AND_AFFAIRS);
            } else {
                $report->setCourtOrderTypeId($allowedCourtOrderTypes[0]);
            }
        }
        $report->setClient($client);

        $form = $this->createForm(new FormDir\ReportType(), $report,
                                  ['action' => $this->generateUrl('report_create', ['clientId' => $clientId])]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $response = $restClient->post('report', $form->getData());

            return $this->redirect($this->generateUrl('report_overview', ['reportId' => $response['report']]));
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template("AppBundle:Overview:overview.html.twig")
     */
    public function overviewAction($reportId)
    {
        // get all the groups (needed by ReportStatusService
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }
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
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        $reportStatusService = new ReportStatusService($report);
        if (!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }
        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted');
        }

        $clients = $this->getUser()->getClients();
        $client = $clients[0];

        $form = $this->createForm(new FormDir\ReportFurtherInfoType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // add furher info
            $this->get('restClient')->put('report/'.$report->getId(), $report, [
                'deserialise_group' => 'furtherInformation',
            ]);

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
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        /** @var TranslatorInterface $translator*/
        $translator = $this->get('translator');

        // check status
        $reportStatusService = new ReportStatusService($report);
        if (!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission', [], 'validators'));
        }
        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted');
        }

        $clients = $this->getUser()->getClients();
        $client = $clients[0];

        $form = $this->createForm(new FormDir\ReportDeclarationType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $this->get('restClient')->put('report/'.$report->getId().'/submit', $report, [
                'deserialise_group' => 'submit',
            ]);

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
            $restClient = $this->get('restClient'); /* @var $restClient RestClient */
            $restClient->post('feedback/report', $form->getData());

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
        $restClient = $this->get('restClient');

        /** @var \AppBundle\Entity\Report $report */
        $report = $this->getReport($reportId, self::$reportGroupsForValidation);

        // check status
        $reportStatusService = new ReportStatusService($report);

        $body = $restClient->get('report/'.$reportId.'/formatted/0', 'raw');

        return [
            'report' => $report,
            'deputy' => $this->getUser(),
            'body' => $body,
            'reportStatus' => $reportStatusService,
        ];
    }

    /**
     * @Route("/report/deputyreport-{reportId}.pdf", name="report_pdf")
     */
    public function pdfAction($reportId)
    {
        $restClient = $this->get('restClient');

        $report = $this->getReport($reportId, ['basic']);
        $pdf = $restClient->get('report/'.$reportId.'/pdf', 'raw');

        $response = new Response($pdf);
        $response->headers->set('Content-Type', 'application/pdf');

        $name = 'OPG102-'.$report->getClient()->getCaseNumber().'-'.date_format($report->getEndDate(), 'Y').'.pdf';

        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($name).'"');
        $response->headers->set('Content-length', $pdf->getSize());

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function groupAssets($assets)
    {
        $assetGroups = array();

        foreach ($assets as $asset) {
            $type = $asset->getTitle();

            if (isset($assetGroups[$type])) {
                $assetGroups[$type][] = $asset;
            } else {
                $assetGroups[$type] = array($asset);
            }
        }

        // sort the assets by their type now.
        ksort($assetGroups);

        return $assetGroups;
    }
}
