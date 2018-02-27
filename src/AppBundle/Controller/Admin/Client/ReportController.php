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
        $reportDueDate = $report->getDueDate();

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

            // TODO move to form ?
            $weeksFromNow = $form['dueDateChoice']->getData();// access unmapped field
            if (!in_array($weeksFromNow, [0, 'other'])) {
                $dueDate = $reportDueDate->modify("+{$weeksFromNow} weeks");
                $report->setDueDate($dueDate);
            }

            //TODO merge API calls into one
            $this->getRestClient()->put('report/' . $report->getId() . '/unsubmit', $report, [
                'startEndDates', 'submitted', 'submit_agreed', 'report_unsubmitted_sections'
            ]);
            $this->getRestClient()->put('report/' . $report->getId(), $report, ['report-due-date']);
            $request->getSession()->getFlashBag()->add('notice', 'Report marked as incomplete');

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'form'     => $form->createView()
        ];
    }

}
