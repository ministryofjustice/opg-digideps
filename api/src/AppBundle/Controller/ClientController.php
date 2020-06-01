<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Repository\ClientRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    /** @var ClientRepository */
    private $repository;

    /**
     * @param ClientRepository $repository
     */
    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Add/Edit a client.
     * When added, the current logged used will be added
     *
     * @Route("/upsert", methods={"POST", "PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function upsertAction(Request $request)
    {
        $data = $this->deserializeBodyContent($request);
        /** @var EntityDir\User|null $user */
        $user = $this->getUser();
        $em = $this->getEntityManager();

        if ($user && $request->getMethod() == 'POST') {
            $client = new EntityDir\Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy(EntityDir\Client::class, $data['id'], 'Client not found');
            if (!$this->isGranted('edit', $client)) {
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

        if ($user && $user->isLayDeputy()) {
            // We come to this route from either editing or creating a client - need to support
            // both routes as an NDR needs to exist for the add client route for Lays
            $ndrRequired = ((array_key_exists('ndr_enabled', $data) && $data['ndr_enabled']) || $user->getNdrEnabled());

            if ($ndrRequired && !$client->getNdr()) {
                $ndr = new EntityDir\Ndr\Ndr($client);
                $em->persist($ndr);
            }

            $client->setCourtDate(new \DateTime($data['court_date']));
            $this->hydrateEntityWithArrayData($client, $data, [
                'case_number' => 'setCaseNumber',
            ]);
        }

        if (array_key_exists('date_of_birth', $data)) {
            $dob = $data['date_of_birth'] ? new \DateTime($data['date_of_birth']) : null;
            $client->setDateOfBirth($dob);
        }

        $em->persist($client);
        $em->flush();

        return ['id' => $client->getId()];
    }

    /**
     * @Route("/{id}", name="client_find_by_id", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param Request $request
     * @param int $id
     * @return null|object
     */
    public function findByIdAction(Request $request, int $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $client = $this->findEntityBy(EntityDir\Client::class, $id);
        if ($client->getArchivedAt()) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        };

        if (!$this->isGranted('view', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $client;
    }

    /**
     * @Route("/{id}/details", name="client_details", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param int $id
     *
     * @return null|object
     *
     */
    public function detailsAction(Request $request, int $id)
    {
        $this->setJmsSerialiserGroups(['client', 'client-users', 'user', 'client-reports', 'client-ndr', 'ndr', 'report', 'status']);

        $result = $this->findEntityBy(EntityDir\Client::class, $id);

        return $result;
    }

    /**
     * @Route("/{id}/archive", name="client_archive", requirements={"id":"\d+"}, methods={"PUT"})
     * @Security("has_role('ROLE_ORG')")
     *
     * @param int $id
     */
    public function archiveAction(Request $request, $id)
    {
        /* @var $client EntityDir\Client */
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!$this->isGranted('edit', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        $client->setArchivedAt(new \DateTime);
        $this->getEntityManager()->flush($client);

        return [
            'id' => $client->getId()
        ];
    }

    /**
     * @Route("/get-all", defaults={"order_by" = "lastname", "sort_order" = "ASC"}, methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAllAction(Request $request)
    {
        $this->setJmsSerialiserGroups(['client', 'active-period']);

        return $this->repository->searchClients(
            $request->get('q'),
            $request->get('order_by'),
            $request->get('sort_order'),
            $request->get('limit'),
            $request->get('offset')
        );
    }

    /**
     * @Route("/{id}/delete", name="client_delete", requirements={"id":"\d+"}, methods={"DELETE"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $client EntityDir\Client */
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        $client->setDeletedAt(new \DateTime());
        $this->getEntityManager()->flush($client);

        return [];
    }
}
