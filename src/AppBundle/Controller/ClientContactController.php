<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("")
 */
class ClientContactController extends RestController
{
    /**
     * @Route("/clients/{clientId}/clientcontacts", name="clientcontact_add")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        echo 'Hello I am the clientContact ADD endpoint (POST)';
        exit;
//        // checks
//        $this->denyAccessUnlessGranted(
//            [
//                EntityDir\User::ROLE_PA,
//                EntityDir\User::ROLE_PA_ADMIN,
//                EntityDir\User::ROLE_PA_TEAM_MEMBER
//            ]
//        );
//        $client = $this->findEntityBy(EntityDir\Client::class, $clientId); /* @var $report EntityDir\Client */
//        $this->denyAccessIfClientDoesNotBelongToUser($client);
//
//        // hydrate and persist
//        $data = $this->deserializeBodyContent($request, [
//            'title' => 'notEmpty',
//            'category' => 'mustExist',
//            'content' => 'mustExist',
//        ]);
//        $note = new EntityDir\Note($client, $data['category'], $data['title'], $data['content']);
//        $note->setCreatedBy($this->getUser());
//        $this->persistAndFlush($note);
//
//        return ['id' => $note->getId()];
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
