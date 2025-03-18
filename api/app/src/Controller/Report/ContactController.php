<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class ContactController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_CONTACTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
    }

    /**
     * @Route("/contact/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['contact'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        return $contact;
    }

    /**
     * @Route("/contact/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function deleteContact($id)
    {
        $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $id, 'Contact not found');
        $report = $contact->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        $this->em->remove($contact);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    /**
     * @Route("/contact", methods={"POST", "PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     **/
    public function upsertContact(Request $request)
    {
        $contactData = $this->formatter->deserializeBodyContent($request);

        if ('POST' == $request->getMethod()) {
            $this->formatter->validateArray($contactData, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $contactData['report_id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $contact = new EntityDir\Report\Contact();
            $contact->setReport($report);
        } else {
            $this->formatter->validateArray($contactData, [
                'id' => 'mustExist',
            ]);
            $contact = $this->findEntityBy(EntityDir\Report\Contact::class, $contactData['id']); /* @var $contact EntityDir\Report\Contact */
            $report = $contact->getReport();
            $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        }

        $this->formatter->validateArray($contactData, [
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
            ->setRelationship($contactData['relationship']);

        $this->em->persist($contact);
        $this->em->flush();

        // remove reason for no contacts
        $report->setReasonForNoContacts(null);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $contact->getId()];
    }

    /**
     * @Route("/{id}/contacts", methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getContacts($id)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $contacts = $this->getRepository(EntityDir\Report\Contact::class)->findByReport($report);

        if (0 == count($contacts)) {
            // throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }

        $this->formatter->setJmsSerialiserGroups(['report', 'contact']);

        return $contacts;
    }
}
