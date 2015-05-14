<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;

class ContactController extends Controller{
   
    /**
     * @Route("/report/{reportId}/contacts/delete/{id}", name="delete_contact")
     */
    public function deleteAction($reportId,$id)
    {
        $util = $this->get('util');
        
        //just do some checks to make sure user is allowed to delete this contact
        $report = $util->getReport($reportId, $this->getUser()->getId(), ['transactions']);
        
        if(!empty($report) && in_array($id, $report->getContacts())){
            $this->get('apiclient')->delete('delete_report_contact', [ 'parameters' => [ 'id' => $id ]]);
        }
        return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));
    }
    
    /**
     * --action[ list, add, edit, delete-confirm, delete ] #default is list
     * 
     * @Route("/report/{reportId}/contacts/{action}/{id}", name="contacts", defaults={ "action" = "list", "id" = " "})
     * @Template()
     */
    public function indexAction($reportId,$action,$id)
    {
        $util = $this->get('util');
       
        $report = $util->getReport($reportId, $this->getUser()->getId(),['transactions']);
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
        
        //set up report submit form
        $reportSubmit = $this->createForm($this->get('form.reportSubmit'));
        
        //set up add reason for no contact form
        $noContact = $this->createForm(new FormDir\ReasonForNoContactType());
        $noContact->setData([ 'reason' => $report->getReasonForNoContacts() ]);
        
        if($request->getMethod() == 'POST'){
            $forms = [ 'contactForm' => $form,
                       'reportSubmit' => $reportSubmit,
                       'noContact' => $noContact ];
            
            $processedForms = $this->handleContactsFormSubmit($forms,$reportId,$action);
            
            if($processedForms instanceof RedirectResponse){
                return $processedForms;
            }
            $form = $processedForms['contactForm'];
            $reportSubmit = $processedForms['reportSubmit'];
            $noContact = $processedForms['noContact'];
        }
        
        return [
            'form' => $form->createView(),
            'contacts' => $contacts,
            'action' => $action,
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $reportSubmit->createView(),
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
        $reportSubmit = $forms['reportSubmit'];
        $noContact    = $forms['noContact'];
        
        $form->handleRequest($request);
        $reportSubmit->handleRequest($request);
        $noContact->handleRequest($request);

        $report = $util->getReport($reportId, $this->getUser()->getId());
        
        //check if contacts form was submitted
        if($form->get('save')->isClicked()){
            if($form->isValid()){
                $contact = $form->getData();
                $contact->setReport($reportId);
                
                if($action == 'add'){
                    $apiClient->postC('add_report_contact', $contact);
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
        }else{
            if($reportSubmit->isValid()){
                if($report->readyToSubmit()){
                    return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                }
            }
        }
        $forms['contactForm'] = $form;
        $forms['reportSubmit'] = $reportSubmit;
        $forms['noContact'] = $noContact;
        
        return $forms;
    }
}