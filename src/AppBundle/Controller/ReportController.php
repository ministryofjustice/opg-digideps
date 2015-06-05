<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Client;
use AppBundle\Service\ApiClient;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use AppBundle\Model\Email;
use AppBundle\Model\EmailAttachment;


class ReportController extends Controller
{
    /**
     * @Route("/report/create/{clientId}", name="report_create")
     * @Template()
     */
    public function createAction($clientId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');
        $util = $this->get('util');
       
        $client = $util->getClient($clientId);
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        //lets check if this  user already has another report, if not start date should be court order date
        $report = new EntityDir\Report();
        $report->setClient($client->getId());
        
        $reports = $client->getReports();
        
        if(empty($reports)){
            $report->setStartDate($client->getCourtDate());
        }
        
        //if client has property & affairs and health & welfare then give them property & affairs
        //else give them health and welfare
        if(count($allowedCourtOrderTypes) > 1){
            $report->setCourtOrderType(EntityDir\Report::PROPERTY_AND_AFFAIRS);
        }else{
            $report->setCourtOrderType($allowedCourtOrderTypes[0]);
        }
        
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
        $report = $util->getReport($reportId);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient());
        
        // report submit logic
        $redirectResponse = $this->get('reportSubmitter')->isReportSubmitted($report);
        
        if($redirectResponse){
            return $redirectResponse;
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
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
            $this->sendByEmail($report);
            
            return $this->redirect($this->generateUrl('report_submit_confirmation', ['reportId'=>$reportId]));
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * @param EntityDir\Report$report
     */
    private function sendByEmail(EntityDir\Report $report)
    {
        //lets send an email to confirm password change
        $emailConfig = $this->container->getParameter('email_report_submit');
        $translator = $this->get('translator');

        $email = new Email();
        $email->setFromEmail($emailConfig['from_email'])
            ->setFromName($translator->trans('reportSubmission.fromName',[], 'email'))
            ->setToEmail($emailConfig['to_email'])
            ->setToName($translator->trans('reportSubmission.toName',[], 'email'))
            ->setSubject($translator->trans('reportSubmission.subject',[], 'email'))
            ->setBodyHtml($this->renderView('AppBundle:Email:report-submission.html.twig'))
            ->setAttachments([new EmailAttachment('report.html', 'application/xml', $this->getReportContent($report))]);

        $this->get('mailSender')->send($email,[ 'html'], 'secure-smtp');
    }
    
    /**
     * @return string
     */
    private function getReportContent(EntityDir\Report $report)
    {
        return $this->forward('AppBundle:Report:display', ['reportId'=>$report->getId(), 'isEmailAttachment'=>true])->getContent();
    }
    
    /**
     * Page displaying the report has been submitted
     * @Route("/report/{reportId}/submitted", name="report_submit_confirmation")
     * @Template()
     */
    public function submitConfirmationAction($reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId);
        // check status
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked', 'submitted']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        $client = $util->getClient($report->getClient());
        
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
        $util = $this->get('util');
        
        $report = $util->getReport($reportId);
        $violations = $this->get('validator')->validate($report, ['due', 'readyforSubmission', 'reviewedAndChecked', 'submitted']);
        if (count($violations)) {
            throw new \RuntimeException($violations->getIterator()->current()->getMessage());
        }
        $client = $util->getClient($report->getClient());
        
        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);
        $decisions = $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'parameters' => [ 'reportId' => $reportId ]]);
        
        return [
            'report' => $report,
            'client' => $client,
            'assets' => $assets,
            'contacts' => $contacts,
            'decisions' => $decisions,
            'isEmailAttachment' => $isEmailAttachment,
            'deputy' => $this->getUser(),
        ];
    }
    
}