<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\User;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
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
        $client = $this->getRestClient()->get('v2/client/' . $id, 'Client');

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
        $client = $this->getRestClient()->get('v2/client/case-number/' . $caseNumber, 'Client');

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
        $client = $this->getRestClient()->get('v2/client/' . $id, 'Client');

        return [
            'client' => $client,
            'namedDeputy' => $this->getNamedDeputy($id, $client)
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", name="admin_client_discharge_confirm", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @param $id
     * @param Logger $logger
     * @param AuditEvents $auditEvents
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dischargeConfirmAction($id, Logger $logger, AuditEvents $auditEvents)
    {
        /** @var Client $client */
        $client = $this->getRestClient()->get('v2/client/' . $id, 'Client');
        $deputy = $this->getNamedDeputy($client->getId(), $client);

        $this->getRestClient()->delete('client/' . $id . '/delete');

        $logger->notice('', $auditEvents->clientDischarged(
            AuditEvents::CLIENT_DISCHARGED_ADMIN_TRIGGER,
            $client->getCaseNumber(),
            $this->getUser()->getEmail(),
            $deputy->getFullName(),
            $client->getCourtDate()
        ));

        return $this->redirectToRoute('admin_client_search');
    }

    /**
     * @param int $id
     * @param Client $client
     * @return NamedDeputy|User|null
     */
    private function getNamedDeputy(int $id, Client $client)
    {
        if (!is_null($client->getNamedDeputy())) {
            return $client->getNamedDeputy();
        }

        if ($client->getDeletedAt() instanceof \DateTime) {
            return null;
        }

        $clientWithUsers = $this->getRestClient()->get('client/' . $id . '/details', 'Client');

        foreach ($clientWithUsers->getUsers() as $user) {
            if ($user->isLayDeputy()) {
                return $clientWithUsers->getUsers()[0];
            }
        }
    }
}
