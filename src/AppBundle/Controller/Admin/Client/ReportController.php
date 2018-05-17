<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportChecklistType;
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
        $reportDueDate = $report->getDueDate();

        if (!$report->getSubmitted()) {
            throw new DisplayableException('Cannot manage active report');
        }

        $form = $this->createForm(UnsubmitReportType::class, $report);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $report
                ->setUnSubmitDate(new \DateTime())
                ->setUnsubmittedSectionsList(implode(',', $report->getUnsubmittedSectionsIds()))
            ;

            $dueDateChoice = $form['dueDateChoice']->getData();
            if ($dueDateChoice == UnsubmitReportType::DUE_DATE_OPTION_CUSTOM) {
                $report->setDueDate($form['dueDateCustom']->getData());
            } elseif (preg_match('/^\d+$/', $dueDateChoice)) {
                $dd = new \DateTime();
                $dd->modify("+{$dueDateChoice} weeks");
                $report->setDueDate($dd);
            }

            $this->getRestClient()->put('report/' . $report->getId() . '/unsubmit', $report, [
                'submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'report_due_date'
            ]);
            $request->getSession()->getFlashBag()->add('notice', 'Report marked as incomplete');

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'reportDueDate'   => $reportDueDate,
            'form'     => $form->createView()
        ];
    }

    /**
     * @Route("checklist", name="admin_report_checklist")
     *
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function checklistAction(Request $request, $id)
    {
        $report = $this->getReport($id, ['report-checklist']);
        \Doctrine\Common\Util\Debug::dump($report->getChecklist(), 2);

        if (!$report->getSubmitted()) {
            throw new DisplayableException('Cannot manage active report');
        }

        $checklist = $report->getChecklist();
        $checklist = empty($checlist) ? new Checklist($report) : $checklist;
        $form = $this->createForm(ReportChecklistType::class, $checklist);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            //$report->setLastCheckedDate(new \DateTime());

            $this->getRestClient()->post('report/' . $report->getId() . '/checked', $checklist, [
                'report-checklist'
            ]);
            $request->getSession()->getFlashBag()->add('notice', 'Report checklist updated');

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'form'     => $form->createView()
        ];
    }
}
