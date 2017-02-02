<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /**
     * Add client.
     *
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function upsertAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'POST') {
            $userId = $data['users'][0];
            if (!in_array($this->getUser()->getId(), [$userId])) {
                throw $this->createAccessDeniedException('User not allowed');
            }
            $user = $this->findEntityBy(EntityDir\User::class, $userId, "User with id: {$userId}  does not exist");
            $client = new EntityDir\Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy(EntityDir\Client::class, $data['id'], 'Client not found');
            if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'case_number' => 'setCaseNumber',
            'address' => 'setAddress',
            'address2' => 'setAddress2',
            'postcode' => 'setPostcode',
            'country' => 'setCountry',
            'county' => 'setCounty',
            'phone' => 'setPhone',
        ]);
        $client->setCourtDate(new \DateTime($data['court_date']));

        $this->persistAndFlush($client);

        //add ODR if not added yet
        if (!$client->getOdr()) {
            $odr = new EntityDir\Odr\Odr($client);
            $this->persistAndFlush($odr);
        }

        return ['id' => $client->getId()];
    }

    /**
     * @param int $userId
     *
     * @return EntityDir\Client
     */
    private function add($userId)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $userId, "User with id: {$userId}  does not exist");

        $client = new EntityDir\Client();
        $client->addUser($user);

        return $client;
    }

    /**
     * @Route("/{id}", name="client_find_by_id" )
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function findByIdAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $client;
    }
}
