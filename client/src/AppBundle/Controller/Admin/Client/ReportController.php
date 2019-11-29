<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\ReportNotSubmittedException;
use AppBundle\Form\Admin\ReviewChecklistType;
use AppBundle\Form\Admin\ReportChecklistType;
use AppBundle\Form\Admin\UnsubmitReportType;
use AppBundle\Form\Admin\UnsubmitReportConfirmType;
use AppBundle\Service\ReportSubmissionService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/admin/report/{id}/", requirements={"id":"\d+"})
 */
class ReportController extends AbstractController
{
    /**
     * JMS groups used for report preview and PDF
     * //TODO consider take/merge the value from ReportController::$reportGroupsAll
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
        'client-named-deputy'
    ];

    /**
     * @Route("manage", name="admin_report_manage")
     * //TODO define Security group (AD to remove?)
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_CASE_MANAGER')")
     * @param Request $request
     * @param string $id
     *
     * @Template("AppBundle:Admin/Client/Report:manage.html.twig")
     *
     * @return array|Response|RedirectResponse
     */
    public function manageAction(Request $request, $id)
    {
        $report = $this->getReport(intval($id), ['report-checklist', 'action']);
        $reportDueDate = $report->getDueDate();

        if (!$report->getSubmitted()) {
            throw new DisplayableException('Only submitted report can be managed');
        }

        $form = $this->createForm(UnsubmitReportType::class, $report);
        $form->handleRequest($request);

        $confirmForm = $this->createForm(UnsubmitReportConfirmType::class);
        $confirmForm->handleRequest($request);

        // edit client form
        if ($confirmForm->isValid()) {
            if ($confirmForm['confirm']->getData() === 'yes') {
                /** @var \DateTime $startDate */
                $startDate = \DateTime::createFromFormat(\DateTime::ISO8601, $confirmForm['startDate']->getData());
                /** @var \DateTime $endDate */
                $endDate = \DateTime::createFromFormat(\DateTime::ISO8601, $confirmForm['endDate']->getData());
                /** @var \DateTime $dueDate */
                $dueDate = \DateTime::createFromFormat(\DateTime::ISO8601, $confirmForm['dueDate']->getData());

                // User confirmed, complete unsubmission
                $report
                    ->setUnSubmitDate(new \DateTime())
                    ->setUnsubmittedSectionsList($confirmForm['unsubmittedSection']->getData())
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setDueDate($dueDate)
                ;

                $this->getRestClient()->put('report/' . $report->getId() . '/unsubmit', $report, [
                    'submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'report_due_date',
                    'startEndDates'
                ]);

                $this->addFlash('notice', 'Report marked as incomplete');

                $unsubmitContent = $this->generateChecklistUnsubmitInformationContent($report);

                // Create Checklist and ChecklistInformation content
                $checklist = $report->getChecklist();
                $checklist = empty($checklist) ? new Checklist($report) : $checklist;
                $checklist->setFurtherInformationReceived($unsubmitContent);

                if (!empty($checklist->getId())) {
                    $this->getRestClient()->put('report/' . $report->getId() . '/checked', $checklist, [
                        'report-checklist', 'checklist-information'
                    ]);
                } else {
                    $this->getRestClient()->post('report/' . $report->getId() . '/checked', $checklist, [
                        'report-checklist', 'checklist-information'
                    ]);
                }
                return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
            } else {
                // User cancelled
                return $this->redirect($this->generateUrl('admin_report_manage', ['id'=>$id]));
            }
        } else if ($form->isValid() || $confirmForm->isSubmitted()) {
            if (!$confirmForm->isSubmitted()) {
                // Populate confirmation form for the first time
                $dueDateChoice = $form['dueDateChoice']->getData();
                if ($dueDateChoice == UnsubmitReportType::DUE_DATE_OPTION_CUSTOM) {
                    $newDueDate = $form['dueDateCustom']->getData();
                } elseif (preg_match('/^\d+$/', $dueDateChoice)) {
                    $newDueDate = new \DateTime();
                    $newDueDate->modify("+{$dueDateChoice} weeks");
                } else {
                    $newDueDate = $report->getDueDate();
                }

                $confirmForm['startDate']->setData($form->getData()->getStartDate()->format(\DateTime::ISO8601));
                $confirmForm['endDate']->setData($form->getData()->getEndDate()->format(\DateTime::ISO8601));
                $confirmForm['dueDate']->setData($newDueDate->format(\DateTime::ISO8601));
                $confirmForm['unsubmittedSection']->setData(implode(',', $report->getUnsubmittedSectionsIds()));
            }

            // Render confirmation form view
            return $this->render('AppBundle:Admin/Client/Report:manageConfirm.html.twig', [
                'report' => $report,
                'form' => $confirmForm->createView(),
                'urlData' => [
                    'startDate' => $form['startDate']->getData()->format('Y-m-d'),
                    'endDate' => $form['endDate']->getData()->format('Y-m-d'),
                    'dueDateChoice' => $form['dueDateChoice']->getData(),
                    'dueDateCustom' => $form['dueDateCustom']->getData() !== null ? $form['dueDateCustom']->getData()->format('Y-m-d') : null,
                    'unsubmittedSection' => $report->getUnsubmittedSectionsIds(),
                ],
            ]);
        }

        // Use URL data
        $dataFromUrl = $request->get('data') ?: [];
        isset($dataFromUrl['startDate']) && $form['startDate']->setData(new \DateTime($dataFromUrl['startDate']));
        isset($dataFromUrl['endDate']) && $form['endDate']->setData(new \DateTime($dataFromUrl['endDate']));
        isset($dataFromUrl['dueDateChoice']) && $form['dueDateChoice']->setData($dataFromUrl['dueDateChoice']);
        isset($dataFromUrl['dueDateCustom']) && $form['dueDateCustom']->setData(new \DateTime($dataFromUrl['dueDateCustom']));
        if (isset($dataFromUrl['unsubmittedSection'])) {
            $unsubmittedSections = $form['unsubmittedSection']->getData();
            foreach ($unsubmittedSections as $section) {
                if (in_array($section->getId(), $dataFromUrl['unsubmittedSection'])) {
                    $section->setPresent(true);
                }
            }
            $form['unsubmittedSection']->setData($unsubmittedSections);
        }

        return [
            'report'   => $report,
            'reportDueDate'   => $reportDueDate,
            'form'     => $form->createView()
        ];
    }

    /**
     * Renders the unsubmit information template and returns the content
     *
     * @param ReportInterface $report
     * @return string
     */
    private function generateChecklistUnsubmitInformationContent(ReportInterface $report)
    {
        /** @var string $content */
        $content = $this->render('AppBundle:Admin/Client/Report/Formatted:unsubmit_information.html.twig', [
            'report' => $report
        ])->getContent();

        return $content;
    }

    /**
     * @Route("checklist", name="admin_report_checklist")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CASE_MANAGER')")
     * @param Request $request
     * @param string $id
     *
     * @Template("AppBundle:Admin/Client/Report:checklist.html.twig")
     *
     * @return array|RedirectResponse|Response
     */
    public function checklistAction(Request $request, $id)
    {
        $report = $this->getReport(
            intval($id),
            array_merge(
                self::$reportGroupsAll,
                [
                    'report-checklist', 'checklist-information', 'last-modified', 'user', 'previous-report-data', 'action'
                ]
            )
        );

        if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
            throw new DisplayableException('Cannot lodge a checklist for an incomplete report');
        }

        $checklist = $report->getChecklist();
        $checklist = empty($checklist) ? new Checklist($report) : $checklist;

        /** @var Form $form */
        $form = $this->createForm(ReportChecklistType::class, $checklist, ['report' => $report]);
        $form->handleRequest($request);

        /** @var SubmitButton $buttonClicked */
        $buttonClicked = $form->getClickedButton();

        $reviewChecklist = $this->getRestClient()->get('report/' . $report->getId() . '/checklist', 'Report\\ReviewChecklist');
        /** @var Form $reviewForm */
        $reviewForm = $this->createForm(ReviewChecklistType::class, $reviewChecklist);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isValid()) {
            /** @var SubmitButton $button */
            $button = $reviewForm->getClickedButton();
            if ($button->getName() === ReviewChecklistType::SUBMIT_ACTION) {
                $reviewChecklist->setIsSubmitted(true);
            }

            if (!empty($reviewChecklist->getId())) {
                $this->getRestClient()->put('report/' . $report->getId() . '/checklist', $reviewChecklist);
            } else {
                $this->getRestClient()->post('report/' . $report->getId() . '/checklist', $reviewChecklist);
            }

            $this->addFlash('notice', 'Review checklist saved');

            return $this->redirect($this->generateUrl('admin_report_checklist', ['id'=>$report->getId()]) . '#anchor-fullReview-checklist');
        }

        if ($buttonClicked instanceof SubmitButton) {
            $checklist->setButtonClicked($buttonClicked->getName());
        }

        if ($form->isValid()) {
            if (!empty($checklist->getId())) {
                $this->getRestClient()->put('report/' . $report->getId() . '/checked', $checklist, [
                    'report-checklist', 'checklist-information'
                ]);
            } else {
                $this->getRestClient()->post('report/' . $report->getId() . '/checked', $checklist, [
                    'report-checklist', 'checklist-information'
                ]);
            }

            /** @var Session $session */
            $session = $request->getSession();

            if (!$session->getFlashBag()->has('notice')) {
                // the duplicate notice is because the PDF view action doesn't actually refresh the page and therefore the original
                // 'saved' notice never gets rendered
                $this->addFlash('notice', 'Lodging checklist saved');
            }

            if ($buttonClicked->getName() == 'saveFurtherInformation') {
                return $this->redirect(
                    $this->generateUrl('admin_report_checklist', ['id'=>$report->getId()]) . '#furtherInformation'
                );
            } else {
                if ($buttonClicked->getName() == 'submitAndContinue') {
                    return $this->redirect($this->generateUrl('admin_report_checklist_submitted', ['id'=>$report->getId()]));
                } else {
                    return $this->redirect($this->generateUrl('admin_report_checklist', ['id'=>$report->getId()]) . '#');
                }
            }
        }

        $costBreakdown = null;

        if ($report->getProfDeputyCostsEstimateHowCharged() !== Report::PROF_DEPUTY_COSTS_TYPE_FIXED) {
            $costBreakdown = $report->generateActualSubmittedEstimateCosts();
        }

        return [
            'report'   => $report,
            'submittedEstimateCosts' => $costBreakdown,
            'form'     => $form->createView(),
            'reviewForm' => $reviewForm->createView(),
            'checklist' => $checklist,
            'reviewChecklist' => $reviewChecklist,
            'previousReportData' => $report->getPreviousReportData()
        ];
    }

    /**
     * @Route("checklist-submitted", name="admin_report_checklist_submitted")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CASE_MANAGER')")
     * @param string $id
     *
     * @Template("AppBundle:Admin/Client/Report:checklistSubmitted.html.twig")
     *
     * @return array
     */
    public function checklistSubmittedAction($id)
    {
        return ['report' => $this->getReport(intval($id))];
    }

    /**
     * Generate and return Checklist as Response object
     *
     * @Route("checklist.pdf", name="admin_checklist_pdf")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CASE_MANAGER')")
     *
     * @param string $id
     * @return Response
     */
    public function checklistPDFViewAction($id)
    {
        $report = $this->getReport(intval($id), array_merge(self::$reportGroupsAll, ['report-checklist', 'checklist-information', 'user']));

        /** @var ReportSubmissionService $reportSubmissionService */
        $reportSubmissionService = $this->get('AppBundle\Service\ReportSubmissionService');
        $pdfBinary = $reportSubmissionService->getChecklistPdfBinaryContent($report);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        if (is_null($report->getEndDate())) {
            throw $this->createNotFoundException();
        }

        $attachmentName = sprintf('DigiChecklist-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() instanceof \DateTime ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }
}
