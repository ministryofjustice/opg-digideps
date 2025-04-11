<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("")
 */
class ClientContactController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    /**
     * @Route("/clients/{clientId}/clientcontacts", name="clientcontact_add", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ORG')")
     */
    public function add(Request $request, $clientId)
    {
        $client = $this->findEntityBy(EntityDir\Client::class, $clientId);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $data = $this->formatter->deserializeBodyContent($request);
        $clientContact = new EntityDir\ClientContact();
        $this->hydrateEntityWithArrayData($clientContact, $data, [
            'first_name' => 'setFirstName',
            'last_name' => 'setLastName',
            'job_title' => 'setJobTitle',
            'phone' => 'setPhone',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'email' => 'setEmail',
            'org_name' => 'setOrgName',
        ]);

        $clientContact->setClient($client);
        $clientContact->setCreatedBy($this->getUser());

        $this->em->persist($clientContact);
        $this->em->flush();

        return ['id' => $clientContact->getId()];
    }

    /**
     * Update contact
     * Only the creator can update the note.
     *
     * @Route("/clientcontacts/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ORG')")
     */
    public function update(Request $request, $id)
    {
        $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
        $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->hydrateEntityWithArrayData($clientContact, $data, [
            'first_name' => 'setFirstName',
            'last_name' => 'setLastName',
            'job_title' => 'setJobTitle',
            'phone' => 'setPhone',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'email' => 'setEmail',
            'org_name' => 'setOrgName',
        ]);
        $this->em->flush($clientContact);

        return $clientContact->getId();
    }

    /**
     * @Route("/clientcontacts/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ORG')")
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups')
            : ['clientcontact', 'clientcontact-client', 'client', 'client-users', 'current-report', 'report-id', 'user'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
        $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

        return $clientContact;
    }

    /**
     * Delete contact
     * Only the creator can delete the note.
     *
     * @Route("/clientcontacts/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_ORG')")
     */
    public function delete($id, LoggerInterface $logger)
    {
        try {
            $clientContact = $this->findEntityBy(EntityDir\ClientContact::class, $id);
            $this->denyAccessIfClientDoesNotBelongToUser($clientContact->getClient());

            $this->em->remove($clientContact);
            $this->em->flush($clientContact);
        } catch (\Throwable $e) {
            $logger->error('Failed to delete client contact ID: '.$id.' - '.$e->getMessage());
        }

        return [];
    }
}
