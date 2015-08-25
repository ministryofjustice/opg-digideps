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
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $safeguard = $report->getSafeguard();
        $request = $this->getRequest();

        // just needed for title etc,
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        $form = $this->createForm(new FormDir\SafeguardingType(), $safeguard);

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

        $form->handleRequest($request);

        if($form->get('save')->isClicked() && $form->isValid()){
            $data = $form->getData();
            $data->keepOnlyRelevantSafeguardingData();

            $this->get('apiclient')->putC('safeguarding/' . $report->getId(),$data);
            $translator = $this->get('translator');

            $this->get('session')->getFlashBag()->add('action', 'page.safeguardinfoSaved');

            return $this->redirect($this->generateUrl('safeguarding', ['reportId'=>$reportId]) . "#pageBody");
        }


        return[ 'report' => $report,
                'client' => $util->getClient($report->getClient(), $this->getUser()->getId()),
                'form' => $form->createView(),
                'report_form_submit' => $this->get('reportSubmitter')->getFormView()
              ];
    }

}
