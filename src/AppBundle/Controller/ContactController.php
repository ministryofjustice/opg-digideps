<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class ContactController extends AbstractController
{

    /**
     * @Route("/report/{reportId}/contacts", name="contacts")
     * @Template("AppBundle:Contact:list.html.twig")
     * @param integer $reportId
     * @return array
     */
    public function listAction($reportId) 
    {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */

        $report = $this->getReportIfReportNotSubmitted($reportId);
        $contacts = $restClient->get('report/' . $reportId . '/contacts', 'Contact[]');
        $client = $this->getClient($report->getClient());

        if (empty($contacts) && $report->isDue() == false) {
            return $this->redirect($this->generateUrl('add_contact', ['reportId'=>$reportId]) );
        }
        
        return [
            'contacts' => $contacts,
            'report' => $report,
            'client' => $client
        ];

    }


    /**
     * @Route("/report/{reportId}/contacts/add", name="add_contact")
     * @Template("AppBundle:Contact:add.html.twig")
     */
    public function addAction(Request $request, $reportId) {

        $report = $this->getReportIfReportNotSubmitted($reportId);

        $contact = new EntityDir\Contact;
        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReport($report->getId());

            $this->get('restClient')->post('report/contact', $data);

            //lets clear any reason for no contacts they might have added previously
            $report->setReasonForNoContacts(null);
            $this->get('restClient')->put('report/'. $report->getId(),$report);

            return $this->redirect($this->generateUrl('contacts', ['reportId'=>$reportId]));
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    /**
     * @Route("/report/{reportId}/contacts/{id}/edit", name="edit_contact")
     * @Template("AppBundle:Contact:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $id) {

        $restClient = $this->get('restClient');

        $report = $this->getReportIfReportNotSubmitted($reportId);

        if (!in_array($id, $report->getContacts())) {
            throw new \RuntimeException("Contact not found.");
        }
        $contact = $restClient->get('report/contact/' . $id, 'Contact');

        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReport($reportId);

            $this->get('restClient')->put('report/contact', $data);

            return $this->redirect($this->generateUrl('contacts', ['reportId'=>$reportId]));
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    /**
     * @Route("/report/{reportId}/contacts/{id}/delete", name="delete_contact")
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        //just do some checks to make sure user is allowed to delete this contact
        $report = $this->getReport($reportId, ['basic']);

        if(!empty($report) && in_array($id, $report->getContacts())){
            $this->get('restClient')->delete("/report/contact/{$id}");
        }

        return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));

    }
    
    /**
     * @Route("/report/{reportId}/contacts/delete-nonereason", name="delete_nonereason_contacts")
     */
    public function deleteReasonAction($reportId)
    {
        //just do some checks to make sure user is allowed to update this report
        $report = $this->getReport($reportId, ['basic', 'transactions']);

        if(!empty($report)){
            $report->setReasonForNoContacts(null);
            $this->get('restClient')->put('report/'.$report->getId(),$report);
        }
        return $this->redirect($this->generateUrl('contacts', ['reportId' => $report->getId()]));
    }
    
    
    /**
     * @Route("/report/{reportId}/contacts/nonereason", name="edit_contacts_nonereason")
     * @Template("AppBundle:Contact:edit_none_reason.html.twig")
     */
    public function noneReasonAction(Request $request, $reportId) {

        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\ReasonForNoContactType(), $report);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $this->get('restClient')->put('report/'. $reportId,$data);

            return $this->redirect($this->generateUrl('contacts', ['reportId'=>$reportId]));

        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    /**
     * Sub controller action called when the no contact form is embedded in another page.
     *
     * @Template("AppBundle:Contact:_none_reason_form.html.twig")
     */
    public function _noneReasonFormAction(Request $request, $reportId)
    {

        $actionUrl = $this->generateUrl('edit_contacts_nonereason', ['reportId'=>$reportId]);
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $form = $this->createForm(new FormDir\ReasonForNoContactType(), $report, ['action' => $actionUrl]);
        $form->handleRequest($request);

        if($form->isValid()){
            $data = $form->getData();
            $this->get('restClient')->put('report/'. $reportId,$data);
        }

        return [
            'form' => $form->createView(),
            'report' => $report
        ];
    }
    
}
