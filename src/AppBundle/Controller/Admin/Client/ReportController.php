<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportChangeDueDateType;
use AppBundle\Form\Admin\UnsubmitReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/report/{id}/", requirements={"id":"\d+"})
 */
class ReportController extends AbstractController
{
    /**
     * @Route("manage", name="admin_report_manage")
     *
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function manageAction(Request $request, $id)
    {
        $report = $this->getReport($id, []);
        if (!$report->getSubmitted()) {
            throw new DisplayableException('Cannot manage active report');
        }

        $form = $this->createForm(UnsubmitReportType::class, $report);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $report
                ->setSubmitted(false)
                ->setAgreedBehalfDeputy(false)
                ->setAgreedBehalfDeputyExplanation(null)
                ->setUnSubmitDate(new \DateTime())
                ->setUnsubmittedSectionsList(implode(',', $report->getUnsubmittedSectionsIds()))
            ;
            $this->getRestClient()->put('report/' . $report->getId() . '/unsubmit', $report, [
                'startEndDates', 'submitted', 'submit_agreed', 'report_unsubmitted_sections'
            ]);
            $request->getSession()->getFlashBag()->add('notice', 'Report unsubmitted');

            return $this->redirect($this->generateUrl('admin_report_change_due_date', ['id'=>$report->getId()]));

        }

        return [
            'report'   => $report,
            'form'     => $form->createView()
        ];
    }


    /**
     * @Route("change-due-date", name="admin_report_change_due_date")
     *
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function changeDueDateAction(Request $request, $id)
    {
        $report = $this->getReport($id, []);
        if ($report->getSubmitted() || !$report->getSubmitDate()) {
            throw new DisplayableException('Can only change due date to unsubmitted report');
        }

        $form = $this->createForm(ReportChangeDueDateType::class, $report);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $weeksFromNow = $form['dueDateChoice']->getData();// access unmapped field
            if (!in_array($weeksFromNow, [0, 'other'])) {
                $dueDate = $report->getDueDate()->modify("+{$weeksFromNow} weeks");
                $report->setDueDate($dueDate);
            }

            $this->getRestClient()->put('report/' . $report->getId(), $report, ['report-due-date']);
            $request->getSession()->getFlashBag()->add('notice', 'Report due date changed');

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'form'     => $form->createView()
        ];
    }
}
