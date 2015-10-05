<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SafeguardController extends Controller{

    /**
     * @Route("/report/{reportId}/safeguarding", name="safeguarding")
     * @Template()
     */
    public function editAction($reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId); // check the report is owned by this user.
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        if ($report->getSafeguarding() == null) {
            $safeguarding = new EntityDir\Safeguarding();
        } else {
            $safeguarding = $report->getSafeguarding();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\SafeguardingType(), $safeguarding);

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

        $form->handleRequest($request);

        if($form->get('save')->isClicked() && $form->isValid()){
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantSafeguardingData();

            if ($safeguarding->getId() == null) {
                $this->get('restClient')->post('safeguarding' , $data, ['deserialise_group' => 'safeguarding']);
            } else {
                $this->get('restClient')->put('safeguarding/'. $safeguarding->getId() ,$data, ['deserialise_group' => 'safeguarding']);
            }

            $this->get('session')->getFlashBag()->add('action', 'page.safeguardinfoSaved');

            return $this->redirect($this->generateUrl('safeguarding', ['reportId'=>$reportId]) . "#pageBody");
        }


        return[ 'report' => $report,
                'client' => $util->getClient($report->getClient()),
                'form' => $form->createView(),
                'report_form_submit' => $this->get('reportSubmitter')->getFormView()
              ];
    }

}
