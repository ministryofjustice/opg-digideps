<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class ContactController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_CONTACTS];

    /**
     * @Route("/contact/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['contact'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        return $contact;
    }

    /**
     * @Route("/contact/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteContact($id)
    {
        $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $id, 'Contact not found');
        $report = $contact->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        $this->getEntityManager()->remove($contact);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/contact")
     * @Method({"POST", "PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     **/
    public function upsertContact(Request $request)
    {
        $contactData = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'POST') {
            $this->validateArray($contactData, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $contactData['report_id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $contact = new EntityDir\Report\Contact();
            $contact->setReport($report);
        } else {
            $this->validateArray($contactData, [
                'id' => 'mustExist',
            ]);
            $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $contactData['id']); /* @var $contact EntityDir\Report\Contact */
            $report = $contact->getReport();
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

        // remove reason for no contacts
        $report->setReasonForNoContacts(null);

        $report->updateSectionsStatusCache($this->sectionIds);

        $this->persistAndFlush($report);

        return ['id' => $contact->getId()];
    }

    /**
     * @Route("/{id}/contacts")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getContacts($id)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $contacts = $this->getRepository(EntityDir\Report\Contact::class)->findByReport($report);

        if (count($contacts) == 0) {
            //throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }

        $this->setJmsSerialiserGroups(['report', 'contact']);

        return $contacts;
    }
}
