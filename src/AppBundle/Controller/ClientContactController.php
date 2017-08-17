<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \Doctrine\Common\Util\Debug as doctrineDebug;

/**
 * @Route("")
 */
class ClientContactController extends RestController
{
    /**
     * @Route("/clients/{clientId}/clientcontacts", name="clientcontact_add")
     * @Method({"POST"})
     */
    public function add(Request $request, $clientId)
    {
        // checks
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA,
                EntityDir\User::ROLE_PA_ADMIN,
                EntityDir\User::ROLE_PA_TEAM_MEMBER
            ]
        );

        $client = $this->findEntityBy(EntityDir\Client::class, $clientId);
        $this->denyAccessIfClientDoesNotBelongToUser($client);

        $data = $this->deserializeBodyContent($request);
        $clientContact = new EntityDir\ClientContact();
        $this->hydrateEntityWithArrayData($clientContact, $data, [
            'firstname'   => 'setFirstName',
            'lastname'    => 'setLastName',
            'job_title'   => 'setJobTitle',
            'phone'       => 'setPhone',
            'address1'    => 'setAddress1',
            'address2'    => 'setAddress2',
            'address3'    => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country'  => 'setAddressCountry',
            'email'       => 'setEmail',
            'org_name'    => 'setOrgName',
        ]);

        $clientContact->setClient($client);
        $clientContact->setCreatedBy($this->getUser());
        $this->persistAndFlush($clientContact);

        return ['id' => $clientContact->getId()];
    }

    /**
     * @Route("/clients/{clientId}/clientcontacts/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        echo 'Hello I am the clientContact by id endpoint (GET)';
        exit;
//        $this->denyAccessUnlessGranted(
//            [
//                EntityDir\User::ROLE_PA,
//                EntityDir\User::ROLE_PA_ADMIN,
//                EntityDir\User::ROLE_PA_TEAM_MEMBER
//            ]
//        );
//
//        $serialisedGroups = $request->query->has('groups')
//            ? (array) $request->query->get('groups') : ['notes', 'user'];
//        $this->setJmsSerialiserGroups($serialisedGroups);
//
//        $note = $this->findEntityBy(EntityDir\Note::class, $id); /* @var $note EntityDir\Note */
//        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());
//
//        return $note;
    }

    /**
     * Update contact
     * Only the creator can update the note
     *
     * @Route("/clients/{clientId}/clientcontacts/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        echo 'Hello I am the clientContact update endpoint (PUT)';
        exit;
//        $this->denyAccessUnlessGranted(
//            [
//                EntityDir\User::ROLE_PA,
//                EntityDir\User::ROLE_PA_ADMIN,
//                EntityDir\User::ROLE_PA_TEAM_MEMBER
//            ]
//        );
//
//        $note = $this->findEntityBy(EntityDir\Note::class, $id); /* @var $note EntityDir\Note */
//
//        // enable if the check above is removed and the note is available for editing for the whole team
//        $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());
//
//        $data = $this->deserializeBodyContent($request);
//        $this->hydrateEntityWithArrayData($note, $data, [
//            'category' => 'setCategory',
//            'title' => 'setTitle',
//            'content' => 'setContent',
//        ]);
//
//        $note->setLastModifiedBy($this->getUser());
//
//        $this->getEntityManager()->flush($note);
//
//        return $note->getId();
    }

    /**
     * Delete contact
     * Only the creator can delete the note
     *
     * @Route("/clients/{clientId}/clientcontacts/{id}")
     * @Method({"DELETE"})
     */
    public function delete($id)
    {
        echo 'Hello I am the clientContact delete endpoint (DELETE)';
        exit;
//        $this->get('logger')->debug('Deleting note ' . $id);
//
//        $this->denyAccessUnlessGranted(
//            [
//                EntityDir\User::ROLE_PA,
//                EntityDir\User::ROLE_PA_ADMIN,
//                EntityDir\User::ROLE_PA_TEAM_MEMBER
//            ]
//        );
//
//        try {
//            /** @var $note EntityDir\Note $note */
//            $note = $this->findEntityBy(EntityDir\Note::class, $id);
//
//            // enable if the check above is removed and the note is available for editing for the whole team
//            $this->denyAccessIfClientDoesNotBelongToUser($note->getClient());
//
//            $this->getEntityManager()->remove($note);
//
//            $this->getEntityManager()->flush($note);
//        } catch (\Exception $e) {
//            $this->get('logger')->error('Failed to delete note ID: ' . $id . ' - ' . $e->getMessage());
//        }
//
//        return [];
    }
}
