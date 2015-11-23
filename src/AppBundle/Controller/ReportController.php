<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;


class ReportController extends AbstractController
{
    /**
     * Create report
     * default action "create" will create only one report (used during registration steps to avoid duplicates when going back from the browser)
     * action "add" will instead add another report
     * 
     * 
     * @Route("/report/{action}/{clientId}", name="report_create",
     *   defaults={ "action" = "create"},
     *   requirements={ "action" = "(create|add)"}
     * )
     * @Template()
     */
    public function createAction($clientId, $action = false)
    {
        $request = $this->getRequest();
        $restClient = $this->get('restClient');
       
        $client = $this->getClient($clientId);
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        $existingReports = $this->getReportsIndexedById($client, ['basic']);
       
        if ($action == 'create' && ($firstReport = array_shift($existingReports)) && $firstReport instanceof EntityDir\Report) {
            $report = $firstReport;
        } else {
            // new report
            $report = new EntityDir\Report();
            
            // check if this  user already has another report, if not start date should be court order date
            $report->setStartDate($client->getCourtDate());
            //if client has property & affairs and health & welfare then give them property & affairs
            //else give them health and welfare
            if(count($allowedCourtOrderTypes) > 1){
                $report->setCourtOrderType(EntityDir\Report::PROPERTY_AND_AFFAIRS);
            }else{
                $report->setCourtOrderType($allowedCourtOrderTypes[0]);
            }
        }
        $report->setClient($client->getId());
        
        
        $form = $this->createForm(new FormDir\ReportType(), $report,
                                  [ 'action' => $this->generateUrl('report_create', [ 'clientId' => $clientId ])]);
        $form->handleRequest($request);
       
        if($form->isValid()){
            $response = $restClient->post('report', $form->getData());
            return $this->redirect($this->generateUrl('report_overview', [ 'reportId' => $response['report'] ]));
        }

        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template("AppBundle:Overview:overview.html.twig")
     */
    public function overviewAction($reportId)
    {
        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());
        $reportStatusService = new ReportStatusService($report, $this->get('translator'));
        
        return [
            'report' => $report,
            'client' => $client,
            'reportStatus' => $reportStatusService,
        ];
    }
    
    /**
     * @Route("/report/{reportId}/add_further_information/{action}", 
     *  name="report_add_further_info", 
     *  defaults={"action": "view"}, 
     *  requirements={"action": "(view|edit)"}
     * )
     * @Template()
     */
    public function furtherInformationAction(Request $request, $reportId, $action = 'view')
    {
        /** @var \AppBundle\Entity\Report $report */
        $report = $this->getReport($reportId, ['basic', 'transactions']); /* @var $report EntityDir\Report */

        /** @var TranslatorInterface $translator*/
        $translator =  $this->get('translator');
        
        // check status
        $reportStatusService = new ReportStatusService($report, $translator);
        if(!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission',[], 'validators'));
        }
        
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportFurtherInfoType, $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // add furher info
            $this->get('restClient')->put('report/' .  $report->getId(), $report, [
                'deserialise_group' => 'furtherInformation',
            ]);
            
            // next or save: redirect to report declration
            if ($form->get('saveAndContinue')->isClicked()) {
                return $this->redirect($this->generateUrl('report_declaration', ['reportId'=>$reportId]));
            }
        }
        
        if (!$report->getFurtherInformation()) {
            $action = 'edit';
        }
        

        
        return [
            'action' => $action,
            'report' => $report,
            'reportStatus' => $reportStatusService,
            'client' => $client,
            'form' => $form->createView()
        ];
    }
    
    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, ['basic' ,'transactions']); /* @var $report EntityDir\Report */
        
        /** @var TranslatorInterface $translator*/
        $translator =  $this->get('translator');

        // check status
        $reportStatusService = new ReportStatusService($report, $translator);
        if(!$report->isDue() || !$reportStatusService->isReadyToSubmit()) {
            throw new \RuntimeException($translator->trans('report.submissionExceptions.readyForSubmission',[], 'validators'));
        }
        
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportDeclarationType(), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $this->get('restClient')->put('report/' .  $report->getId() . '/submit', $report, [
                'deserialise_group' => 'submit',
            ]);
            
            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId'=>$report->getId()]));
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    
    /**
     * Page displaying the report has been submitted
     * @Route("/report/{reportId}/submitted", name="report_submit_confirmation")
     * @Template()
     */
    public function submitConfirmationAction($reportId)
    {
        $report = $this->getReport($reportId, ['basic' ,'transactions']);

        /** @var TranslatorInterface $translator*/
        $translator =  $this->get('translator');
        
        // check status
        if(!$report->getSubmitted() ) {
            throw new \RuntimeException($translator->trans('submissionExceptions.submitted',[], 'validators'));
        }


        $client = $this->getClient($report->getClient());

        $form = $this->createForm('feedback_report', new ModelDir\FeedbackReport());
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $restClient = $this->get('restClient'); /* @var $restClient RestClient */
            $restClient->post('feedback/report', $form->getData());

            return $this->redirect($this->generateUrl('report_submit_feedback', ['reportId' => $reportId]));
        }


        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView()
        ];
    }
    
    /**
     * @Route("/report/{reportId}/submit_feedback", name="report_submit_feedback")
     * @Template()
     */
    public function submitFeedbackAction($reportId)
    {
        $report = $this->getReport($reportId, ['basic', 'transactions']);
        
        /** @var TranslatorInterface $translator*/
        $translator =  $this->get('translator');

        // check status
        if(!$report->getSubmitted() ) {
            throw new \RuntimeException($translator->trans('submissionExceptions.submitted',[], 'validators'));
        }
        
        $client = $this->getClient($report->getClient());

        return [
            'report' => $report,
            'client' => $client,
        ];
    }


    /**
     * @Route("/report/{reportId}/display", name="report_display")
     * @Template()
     */
    public function displayAction($reportId, $isEmailAttachment = false)
    {
        $restClient = $this->get('restClient');
        
        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        $client = $this->getClient($report->getClient());
        
        $contacts = $restClient->get('report/' . $reportId . '/contacts', 'Contact[]');
        $decisions = $restClient->get('report/' . $reportId . '/decisions', 'Decision[]');
        $assets = $restClient->get('report/' . $reportId . '/assets', 'Asset[]');
        
        return [
            'report' => $report,
            'client' => $client,
            'contacts' => $contacts,
            'decisions' => $decisions,
            'assets' => $assets,
            'isEmailAttachment' => $isEmailAttachment,
            'deputy' => $this->getUser(),
        ];
    }

    private function groupAssets($assets)
    {
        $assetGroups = array();
        
        foreach ($assets as $asset) {
        
            $type = $asset->getTitle();
        
            if (isset($assetGroups[$type])) {
                $assetGroups[$type][] = $asset;
            } else {
                $assetGroups[$type] = array($asset);
            }
        }
    
        // sort the assets by their type now.
        ksort($assetGroups);
    
        return $assetGroups;
    }
    
}
