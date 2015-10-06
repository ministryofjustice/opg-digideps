<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;


class ContactController extends RestController
{
     /**
     * @Route("/report/get-contact/{id}")
     * @Method({"GET"})
     */
    public function getContactAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $serialisedGroups = $request->query->has('groups') ? (array)$request->query->get('groups') : ['basic'];
        $this->setJmsSerialiserGroups($serialisedGroups);
        
        $contact = $this->findEntityBy('Contact', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        
        return $contact;
    }
    
    /**
     * 
     * @Route("report/delete-contact/{id}")
     * @Method({"DELETE"})
     */
    public function deleteContactAction($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $contact = $this->findEntityBy('Contact', $id, 'Contact not found');
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        
        $this->getEntityManager()->remove($contact);
        $this->getEntityManager()->flush();
        
        return [ ];
    }
    
     /**
     * @Route("/report/upsert-contact")
     * @Method({"POST", "PUT"})
     **/
    public function upsertContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $contactData = $this->deserializeBodyContent($request);
       
        $report = $this->findEntityBy('Report',$contactData['report']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        if($request->getMethod() == "POST"){
            $contact = new EntityDir\Contact();
            $contact->setReport($report);
        }else{
            $contact = $this->findEntityBy('Contact', $contactData['id']);
            
            if(empty($contact)){
                throw new AppExceptions\NotFound("Contact with id: ".$contactData['id'], 404);
            }
        }
        $contact->setContactName($contactData['contact_name']);
        $contact->setAddress($contactData['address']);
        $contact->setAddress2($contactData['address2']);
        $contact->setCounty($contactData['county']);
        $contact->setPostcode($contactData['postcode']);
        $contact->setCountry($contactData['country']);
        $contact->setExplanation($contactData['explanation']);
        $contact->setRelationship($contactData['relationship']);
        $contact->setLastedit(new \DateTime());
        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush();
        
        return [ 'id' => $contact->getId() ];
    }
    
     
    /**
     * @Route("/report/get-contacts/{id}")
     * @Method({"GET"})
     */
    public function getContactsAction($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        $contacts = $this->getRepository('Contact')->findByReport($report);
        
        if(count($contacts) == 0){
            //throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }
        return $contacts;
    }
}