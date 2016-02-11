<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\ReportStatusService;


class ConcernController extends AbstractController{

    /**
     * @Route("/report/{reportId}/concerns", name="concerns")
     * @Template()
     */
    public function editAction($reportId)
    {
        $report = $this->getReport($reportId, [ "basic"]); // check the report is owned by this user.
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        if ($report->getConcern() == null) {
            $concern = new EntityDir\Concern();
        } else {
            $concern = $report->getConcern();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\ConcernType(), $concern);

        $form->handleRequest($request);

        if($form->get('save')->isClicked() && $form->isValid()){
            $data = $form->getData();
            $data->setReport($report);

            if ($concern->getId() == null) {
                $this->get('restClient')->post('report/concern' , $data, ['deserialise_group' => 'Default']);
            } else {
                $this->get('restClient')->put('report/concern/'. $concern->getId() ,$data, ['deserialise_group' => 'Default']);
            }

            return $this->redirect($this->generateUrl('concerns', ['reportId'=>$reportId]) . "#pageBody");
        }

        $reportStatusService = new ReportStatusService($report, $this->get('translator'));
        
        return[ 'report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }

}
