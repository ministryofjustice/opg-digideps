<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContactController extends Controller{

    /**
     * @Route("/report/{reportId}/contacts/delete-reason", name="delete_reason_contacts")
     */
    public function deleteReasonAction($reportId)
    {
        $util = $this->get('util');

        //just do some checks to make sure user is allowed to update this report
        $report = $util->getReport($reportId,['transactions']);

        if(!empty($report)){
            $report->setReasonForNoContacts(null);
            $this->get('apiclient')->putC('report/'.$report->getId(),$report);
        }
        
        return $this->redirect($this->generateUrl('contacts', ['reportId' => $report->getId()]));
    }
    
    
    /**
     * @Route("/report/{reportId}/contacts/delete/{id}", name="delete_contact")
     */
    public function deleteAction($reportId,$id)
    {
        $util = $this->get('util');

        //just do some checks to make sure user is allowed to delete this contact
        $report = $util->getReport($reportId,['transactions']);

        if(!empty($report) && in_array($id, $report->getContacts())){
            $this->get('apiclient')->delete('delete_report_contact', [ 'parameters' => [ 'id' => $id ]]);
        }
        return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));
    }

    /**
     * --action[ list, add, edit, delete-confirm, delete, delete-reason-confirm ] #default is list
     *
     * @Route("/report/{reportId}/contacts/{action}/{id}", name="contacts", defaults={ "action" = "list", "id" = " "})
     * @Template()
     */
    public function indexAction($reportId,$action,$id)
    {
        $util = $this->get('util');

        $report = $util->getReport($reportId,['transactions']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient());

        $request = $this->getRequest();

        $apiClient = $this->get('apiclient');
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);

        if(in_array($action, [ 'edit', 'delete-confirm'])){
            $contact = $apiClient->getEntity('Contact','get_report_contact', [ 'parameters' => ['id' => $id ]]);
            $form = $this->createForm(new FormDir\ContactType(), $contact, [ 'action' => $this->generateUrl('contacts', [ 'reportId' => $reportId, 'action' => 'edit', 'id' => $id ])]);
        }else{
             //set up add contact form
            $contact = new EntityDir\Contact();
            $form = $this->createForm(new FormDir\ContactType(), $contact, [ 'action' => $this->generateUrl('contacts',[ 'reportId' => $reportId, 'action' => 'add' ]) ]);
        }
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        //set up add reason for no contact form
        $noContact = $this->createForm(new FormDir\ReasonForNoContactType(), null, [ 'action' => $this->generateUrl('contacts', [ 'reportId' => $reportId])."#pageBody" ]);
        $reason = $report->getReasonForNoContacts();
        $mode = empty($reason)? 'add':'edit';
        $noContact->setData([ 'reason' => $reason, 'mode' => $mode ]);

        if($request->getMethod() == 'POST'){
            $forms = [ 'contactForm' => $form,
                       'noContact' => $noContact ];

            $processedForms = $this->handleContactsFormSubmit($forms,$reportId,$action);

            if($processedForms instanceof RedirectResponse){
                return $processedForms;
            }
            $form = $processedForms['contactForm'];
            $noContact = $processedForms['noContact'];
        }

        return [
            'form' => $form->createView(),
            'contacts' => $contacts,
            'action' => $action,
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView(),
            'no_contact' => $noContact->createView() ];
    }

    /**
    * @param array $forms
    * @return array $forms
    */
    private function handleContactsFormSubmit(array $forms, $reportId, $action='add')
    {
        $util = $this->get('util');

        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');

        $form = $forms['contactForm'];
        $noContact    = $forms['noContact'];

        $form->handleRequest($request);
        $noContact->handleRequest($request);

        $report = $util->getReport($reportId);
        
        //check if contacts form was submitted
        if($form->get('save')->isClicked()){
            if($form->isValid()){
                $contact = $form->getData();
                $contact->setReport($reportId);

                if($action == 'add'){
                    $apiClient->postC('add_report_contact', $contact);
                    
                    //lets clear any reason for no decisions they might have added previously
                    $report->setReasonForNoContacts(null);
                    $apiClient->putC('report/'.$report->getId(),$report);
            
                }else{
                    $apiClient->putC('update_report_contact', $contact);
                }

                return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));
            }

         //check if add reason for no contact form was submitted
        }elseif($noContact->get('saveReason')->isClicked()){
            if($noContact->isValid()){
                 $formData = $noContact->getData();

                 $report->setReasonForNoContacts($formData['reason']);

                 $apiClient->putC('report/'.$report->getId(),$report);

                 return $this->redirect($this->generateUrl('contacts',[ 'reportId' => $report->getId()]));
            }

        //the above 2 forms test false then submission was for the overall report submit
        }
        $forms['contactForm'] = $form;
        $forms['noContact'] = $noContact;

        return $forms;
    }
}
