<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Event\ClientArchivedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Repository\ClientRepository;
use App\Service\Audit\AuditEvents;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    public function __construct(
        private ClientRepository $repository,
        private EntityManagerInterface $em,
        private RestFormatter $formatter,
        private ObservableEventDispatcher $eventDispatcher,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * Add/Edit a client.
     * When added, the current logged used will be added.
     *
     * @Route("/upsert", methods={"POST", "PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function upsertAction(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request);
        /** @var EntityDir\User|null $user */
        $user = $this->getUser();

        if ($user && 'POST' == $request->getMethod()) {
            $client = new EntityDir\Client();
            $client->addUser($user);
        } else {
            $client = $this->findEntityBy(EntityDir\Client::class, $data['id'], 'Client not found');
            if (!$this->isGranted('edit', $client)) {
                if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                    throw $this->createAccessDeniedException('Client does not belong to user');
                }
            }
        }

        $this->hydrateEntityWithArrayData($client, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'address' => 'setAddress',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address4' => 'setAddress4',
            'address5' => 'setAddress5',
            'postcode' => 'setPostcode',
            'country' => 'setCountry',
            'phone' => 'setPhone',
            'email' => 'setEmail',
        ]);

        if ($user && $user->isLayDeputy()) {
            // We come to this route from either editing or creating a client - need to support
            // both routes as an NDR needs to exist for the add client route for Lays
            $ndrRequired = ((array_key_exists('ndr_enabled', $data) && $data['ndr_enabled']) || $user->getNdrEnabled());

            if ($ndrRequired && !$client->getNdr()) {
                $ndr = new EntityDir\Ndr\Ndr($client);
                $this->em->persist($ndr);
            }

            $client->setCourtDate(new \DateTime($data['court_date']));
            $this->hydrateEntityWithArrayData($client, $data, [
                'case_number' => 'setCaseNumber',
                'deputy' => 'setDeputy',
            ]);
        }

        if (array_key_exists('date_of_birth', $data)) {
            $dob = $data['date_of_birth'] ? new \DateTime($data['date_of_birth']) : null;
            $client->setDateOfBirth($dob);
        }

        $this->em->persist($client);
        $this->em->flush();

        return ['id' => $client->getId()];
    }

    /**
     * @Route("/{id}", name="client_find_by_id", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     *
     * @return object|null
     */
    public function findByIdAction(Request $request, int $id)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $client = $this->findEntityBy(EntityDir\Client::class, $id);
        if ($client->getArchivedAt()) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        }

        if (!$this->isGranted('view', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        return $client;
    }

    /**
     * @Route("/{id}/details", name="client_details", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return object|null
     */
    public function detailsAction(Request $request, int $id)
    {
        if ($request->query->has('groups')) {
            $serialisedGroups = (array) $request->query->get('groups');
        } else {
            $serialisedGroups = [
                'client',
                'client-users',
                'user',
                'client-reports',
                'client-ndr',
                'ndr',
                'report',
                'status',
                'client-organisations',
                'organisation',
            ];
        }

        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $result = $this->findEntityBy(EntityDir\Client::class, $id);

        return $result;
    }

    /**
     * @Route("/{id}/archive", name="client_archive", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ORG')")
     *
     * @param int $id
     */
    public function archiveAction(Request $request, $id)
    {
        /* @var $client EntityDir\Client */
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        if (!$this->isGranted('edit', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        $client->setArchivedAt(new \DateTime());
        $this->em->flush($client);

        $trigger = AuditEvents::TRIGGER_USER_ARCHIVED_CLIENT;
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $clientArchivedEvent = new ClientArchivedEvent(
            $client,
            $currentUser,
            $trigger
        );
        $this->eventDispatcher->dispatch($clientArchivedEvent, ClientArchivedEvent::NAME);

        return [
            'id' => $client->getId(),
        ];
    }

    /**
     * @Route("/get-all", defaults={"order_by" = "lastname", "sort_order" = "ASC"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getAllAction(Request $request)
    {
        $this->formatter->setJmsSerialiserGroups(['client', 'active-period']);

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
     *
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     */
    public function deleteAction(Request $request, $id)
    {
        /* @var $client EntityDir\Client */
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        $client->setDeletedAt(new \DateTime());
        $this->em->flush($client);

        return [];
    }

    /**
     * @Route("/{id}/unarchive", methods={"PUT"}, requirements={"id":"\d+"})
     *
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     */
    public function unarchiveClientAction(int $id)
    {
        $client = $this->findEntityBy(EntityDir\Client::class, $id);

        $client->setArchivedAt(null);
        $this->em->flush($client);

        return [
            'id' => $client->getId(),
        ];
    }

    /**
     * @Route("/{id}/update-deputy/{deputyId}", methods={"PUT"}, requirements={"id":"\d+", "deputyId":"\d+"})
     *
     * @Security("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')")
     */
    public function updateDeputyAction(Request $request, int $id, int $deputyId)
    {
        $client = $this->findEntityBy(EntityDir\Client::class, $id);
        $deputy = $this->findEntityBy(EntityDir\Deputy::class, $deputyId);

        $client->setDeputy($deputy);
        $this->em->persist($client);
        $this->em->flush();

        return ['clientId' => $id, 'deputyId' => $deputyId];
    }

    /**
     * Endpoint for getting the clients for a deputy uid.
     *
     * @Route("/get-all-clients-by-deputy-uid/{deputyUid}", methods={"GET"})
     *
     * @throws \Exception
     */
    public function getAllClientsByDeputyUid(Request $request, int $deputyUid)
    {
        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['client'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $this->repository->getAllClientsAndReportsByDeputyUid($deputyUid);
    }
}
