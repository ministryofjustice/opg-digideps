<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MentalCapacityController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/mental-capacity", name="mental_capacity")
     * @Template()
     * 
     * @param int $reportId
     *
     * @return array
     */
    public function editAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'mental-capacity']);

        $mc = $report->getMentalCapacity();
        if ($mc == null) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\Report\MentalCapacityType(), $mc);

        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->put('report/'.$reportId.'/mental-capacity', $data, [
                'deserialise_group' => 'mental-capacity',
            ]);

            return $this->redirect($this->generateUrl('mental_capacity', ['reportId' => $reportId]).'#pageBody');
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
        ];
    }
}
