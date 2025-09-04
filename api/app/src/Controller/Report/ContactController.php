<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\Contact;
use App\Entity\Report\Report;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/report')]
class ContactController extends RestController
{
    private array $sectionIds = [Report::SECTION_CONTACTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/contact/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): Contact
    {
        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['contact'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $contact = $this->findEntityBy(Contact::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        return $contact;
    }

    #[Route(path: '/contact/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteContact(int $id): array
    {
        $contact = $this->findEntityBy(Contact::class, $id, 'Contact not found');
        $report = $contact->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());

        $this->em->remove($contact);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    #[Route(path: '/contact', methods: ['POST', 'PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function upsertContact(Request $request): array
    {
        $contactData = $this->formatter->deserializeBodyContent($request);

        if ('POST' == $request->getMethod()) {
            $this->formatter->validateArray($contactData, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(Report::class, $contactData['report_id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $contact = new Contact();
            $contact->setReport($report);
        } else {
            $this->formatter->validateArray($contactData, [
                'id' => 'mustExist',
            ]);
            $contact = $this->findEntityBy(Contact::class, $contactData['id']); /* @var $contact \App\Entity\Report\Contact */
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

    #[Route(path: '/{id}/contacts', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getContacts(int $id): array
    {
        $report = $this->findEntityBy(Report::class, $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $contacts = $this->em->getRepository(Contact::class)->findByReport($report);

        if (0 == count($contacts)) {
            // throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }

        $this->formatter->setJmsSerialiserGroups(['report', 'contact']);

        return $contacts;
    }
}
