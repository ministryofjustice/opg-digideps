<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /**
     * Add/Edit a client.
     * When added, the current logged used will be added
     *
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function upsertAction(Request $request)
    {
        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'POST') {
            $user = $this->getUser();
            $client = new EntityDir\Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy(EntityDir\Client::class, $data['id'], 'Client not found');
            if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname'   => 'setFirstname',
            'lastname'    => 'setLastname',
            'address'     => 'setAddress',
            'address2'    => 'setAddress2',
            'postcode'    => 'setPostcode',
            'country'     => 'setCountry',
            'county'      => 'setCounty',
            'phone'       => 'setPhone',
            'email'       => 'setEmail',
        ]);

        if ($this->getUser()->isLayDeputy()) {
            $client->setCourtDate(new \DateTime($data['court_date']));
            $this->hydrateEntityWithArrayData($client, $data, [
                'case_number' => 'setCaseNumber',
            ]);
        }

        if (array_key_exists('date_of_birth', $data)) {
            $dob = $data['date_of_birth'] ? new \DateTime($data['date_of_birth']) : null;
            $client->setDateOfBirth($dob);
        }

        $this->persistAndFlush($client);

        //add NDR if not added yet
        // TODO move to listener or service
        if (!$client->getNdr()) {
            $ndr = new EntityDir\Ndr\Ndr($client);
            $this->persistAndFlush($ndr);
        }

        return ['id' => $client->getId()];
    }

    /**
     * @Route("/{id}", name="client_find_by_id", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function findByIdAction(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $client;
    }

    /**
     * @Route("/{id}/details", name="client_details", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param $id
     *
     * @return null|object
     *
     */
    public function detailsAction(Request $request, $id)
    {
        $this->setJmsSerialiserGroups(['client', 'client-users', 'user', 'report', 'client-reports', 'status', 'ndr']);

        $result = $this->findEntityBy(EntityDir\Client::class, $id);

        return $result;
    }

    /**
     * @Route("/{id}/archive", name="client_archive", requirements={"id":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_PA')")
     *
     * @param int $id
     */
    public function archiveAction(Request $request, $id)
    {
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        foreach ($client->getUsers() as $user) {
            $client->removeUser($user);
        }
        $this->persistAndFlush($client);

        return [
            'id' => $client->getId()
        ];
    }

    /**
     * @Route("/get-all", defaults={"order_by" = "lastname", "sort_order" = "ASC"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAll(Request $request)
    {
        $this->setJmsSerialiserGroups(['client']);

        return $this->getRepository(EntityDir\Client::class)->searchClients(
            $request->get('q'),
            $request->get('order_by'),
            $request->get('sort_order'),
            $request->get('limit'),
            $request->get('offset')
        );

    }
}
