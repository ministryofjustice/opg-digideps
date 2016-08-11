<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;

class ActionController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/actions", name="actions")
     * @Template()
     */
    public function editAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['action']); // check the report is owned by this user.
        if ($report->getAction() == null) {
            $action = new EntityDir\Report\Action();
        } else {
            $action = $report->getAction();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\Report\ActionType(), $action);

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->put('report/'.$reportId.'/action', $data);

            return $this->redirect($this->generateUrl('actions', ['reportId' => $reportId]).'#pageBody');
        }

        $reportStatusService = new ReportStatusService($report);

        return['report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }
}
