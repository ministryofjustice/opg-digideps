<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends Controller
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
        $apiClient = $this->get('apiclient');
        $util = $this->get('util');
       
        $client = $util->getClient($clientId, $this->getUser()->getId());
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        $existingReports = $util->getReportsIndexedById($this->getUser()->getId(), $client, ['basic']);
       
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
       
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $response = $apiClient->postC('add_report', $form->getData());
                return $this->redirect($this->generateUrl('report_overview', [ 'reportId' => $response['report'] ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        $this->get('session')->getFlashBag()->add('news', 'report.header.announcement');
        
        return [
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
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
        $report = $this->get('util')->getReport($reportId, $this->getUser()->getId()); /* @var $report EntityDir\Report */
        
        // check status
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportFurtherInfoType, $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // add furher info
            $this->get('apiclient')->putC('report/' .  $report->getId(), $report, [
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
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $report = $this->get('util')->getReport($reportId, $this->getUser()->getId()); /* @var $report EntityDir\Report */
        // check status
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportDeclarationType());
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $report->setSubmitted(true)->setSubmitDate(new \DateTime());
            $this->get('apiclient')->putC('report/' .  $report->getId(), $report, [
                'deserialise_group' => 'submit',
            ]);
            
            // send report by email
            $reportContent = $this->forward('AppBundle:Report:formatted', ['reportId'=>$report->getId(), 'isEmailAttachment'=>true])->getContent();
            $reportEmail = $this->get('mailFactory')->createReportEmail($client, $reportContent);
            $this->get('mailSender')->send($reportEmail,[ 'html'], 'secure-smtp');
            
            //lets create subsequent year's report
            $response = $this->get('apiclient')->postC('clone_report', $report);
            
            $newReport = $this->get('util')->getReport($response['report'], $this->getUser()->getId()); /* @var $report EntityDir\Report */
            
            //send confirmation email
            $reportConfirmEmail = $this->get('mailFactory')->createReportSubmissionConfirmationEmail($this->getUser(), $report, $newReport);
            $this->get('mailSender')->send($reportConfirmEmail, [ 'text', 'html']);
            
            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId'=>$reportId]));
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
        $util = $this->get('util');
        $report = $util->getReport($reportId, $this->getUser()->getId());
        // check status
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked', 'submitted']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());


        $form = $this->createForm(new FormDir\FeedbackType(), new ModelDir\Feedback());
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $feedbackEmail = $this->get('mailFactory')->createFeedbackEmail($form->getData());
            $this->get('mailSender')->send($feedbackEmail, [ 'html']);

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
        $util = $this->get('util');
        $report = $util->getReport($reportId, $this->getUser()->getId());
        // check status
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked', 'submitted']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());

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
        $apiClient = $this->get('apiclient');
        $util = $this->get('util'); /* @var $util \AppBundle\Service\Util */
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);
        $decisions = $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'parameters' => [ 'reportId' => $reportId ]]);
        
        return [
            'report' => $report,
            'client' => $client,
            'contacts' => $contacts,
            'decisions' => $decisions,
            'isEmailAttachment' => $isEmailAttachment,
            'deputy' => $this->getUser(),
        ];
    }
    
    /**
     * @Route("/report/{reportId}/formatted", name="formatted_report_display")
     * @Template()
     */
    public function formattedAction($reportId, $isEmailAttachment = false)
    {
        $apiClient = $this->get('apiclient');
        $util = $this->get('util');
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());
        
        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);
        $groupAssets = $this->groupAssets($assets);
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);
        $decisions = $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'parameters' => [ 'reportId' => $reportId ]]);
        
        return [
            'report' => $report,
            'client' => $client,
            'assets' => $assets,
            'groupAssets' => $groupAssets,
            'contacts' => $contacts,
            'decisions' => $decisions,
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
