<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Form\Admin\UnsubmitReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

        $form = $this->createForm(UnsubmitReportType::class, $report);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $report
                ->setSubmitted(false)
                ->setUnSubmitDate(new \DateTime())
            ;

            $this->getRestClient()->put('report/' . $report->getId().'/unsubmit', $report, ['startEndDates', 'submit', 'unsubmit_date']);
            $request->getSession()->getFlashBag()->add('notice', "Report unsubmitted");

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'form'     => $form->createView()
        ];
    }

}
