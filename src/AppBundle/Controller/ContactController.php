<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;

/**
 * @Route("/report")
 */
class ContactController extends RestController
{
    /**
     * @Route("/contact/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['basic'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $contact = $this->findEntityBy('Contact', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        return $contact;
    }

    /**
     * @Route("/contact/{id}")
     * @Method({"DELETE"})
     */
    public function deleteContact($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $contact = $this->findEntityBy('Contact', $id, 'Contact not found');
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        $this->getEntityManager()->remove($contact);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/contact")
     * @Method({"POST", "PUT"})
     **/
    public function upsertContact(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $contactData = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'POST') {
            $this->validateArray($contactData, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy('Report', $contactData['report_id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $contact = new EntityDir\Contact();
            $contact->setReport($report);
        } else {
            $this->validateArray($contactData, [
                'id' => 'mustExist',
            ]);
            $contact = $this->findEntityBy('Contact', $contactData['id']);
            $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        }

        $this->validateArray($contactData, [
            'contact_name' => 'mustExist',
            'address' => 'mustExist',
            'address2' => 'mustExist',
            'county' => 'mustExist',
            'postcode' => 'mustExist',
            'country' => 'mustExist',
            'explanation' => 'mustExist',
            'relationship' => 'mustExist',
        ]);

        $contact->setContactName($contactData['contact_name'])
            ->setAddress($contactData['address'])
            ->setAddress2($contactData['address2'])
            ->setCounty($contactData['county'])
            ->setPostcode($contactData['postcode'])
            ->setCountry($contactData['country'])
            ->setExplanation($contactData['explanation'])
            ->setRelationship($contactData['relationship'])
            ->setLastedit(new \DateTime());

        $this->persistAndFlush($contact);

        return ['id' => $contact->getId()];
    }

    /**
     * @Route("/{id}/contacts")
     * @Method({"GET"})
     */
    public function getContacts($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $contacts = $this->getRepository('Contact')->findByReport($report);

        if (count($contacts) == 0) {
            //throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }

        return $contacts;
    }
}
