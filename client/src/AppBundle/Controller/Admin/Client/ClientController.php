<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\User;
use AppBundle\Event\ClientDeletedEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\Internal\ClientApi;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
    /** @var RestClient */
    private $restClient;

    /** @var ClientApi */
    private $clientApi;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RestClient $restClient,
        ClientApi $clientApi,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->restClient = $restClient;
        $this->clientApi = $clientApi;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/{id}/details", name="admin_client_details", requirements={"id":"\d+"})
     * //TODO define Security group (AD to remove?)
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @param string $id
     *
     * @Template("AppBundle:Admin/Client/Client:details.html.twig")
     *
     * @return array
     */
    public function detailsAction($id)
    {
        $client = $this->restClient->get('v2/client/' . $id, 'Client');

        return [
            'client'      => $client,
            'namedDeputy' => $this->getNamedDeputy($id, $client)
        ];
    }

    /**
     * @Route("/case-number/{caseNumber}/details", name="admin_client_by_case_number_details")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @param string $caseNumber
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detailsByCaseNumberAction($caseNumber)
    {
        $client = $this->restClient->get('v2/client/case-number/' . $caseNumber, 'Client');

        return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
    }

    /**
     * @Route("/{id}/discharge", name="admin_client_discharge", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @param $id
     *
     * @Template("AppBundle:Admin/Client/Client:discharge.html.twig")
     *
     * @return array
     */
    public function dischargeAction($id)
    {
        $client = $this->restClient->get('v2/client/' . $id, 'Client');

        return [
            'client' => $client,
            'namedDeputy' => $this->getNamedDeputy($id, $client)
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", name="admin_client_discharge_confirm", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     * @param $id
     * @param AuditEvents $auditEvents
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function dischargeConfirmAction($id)
    {
        $clientWithUsers = $this->clientApi->getWithUsers($id);
        $deputy = $this->getNamedDeputy($clientWithUsers);

        $this->restClient->delete('client/' . $id . '/delete');

        $clientDeletedEvent = new ClientDeletedEvent(
            $clientWithUsers,
            $this->getUser(),
            $deputy,
            AuditEvents::TRIGGER_ADMIN_BUTTON
        );

        $this->eventDispatcher->dispatch($clientDeletedEvent, ClientDeletedEvent::NAME);

        return $this->redirectToRoute('admin_client_search');
    }

    /**
     * @param Client $client
     * @return NamedDeputy|User|null
     */
    private function getNamedDeputy(Client $clientWithUsers)
    {
        if (!is_null($clientWithUsers->getNamedDeputy())) {
            return $clientWithUsers->getNamedDeputy();
        }

        if ($clientWithUsers->getDeletedAt() instanceof \DateTime) {
            return null;
        }

        foreach ($clientWithUsers->getUsers() as $user) {
            if ($user->isLayDeputy()) {
                return $clientWithUsers->getUsers()[0];
            }
        }
    }
}
