<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/{id}/details", name="admin_client_details", requirements={"id":"\d+"})
     * //TODO define Security group (AD to remove?)
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_CASE_MANAGER')")
     * @param Request $request
     * @param $id
     *
     * @Template("AppBundle:Admin/Client/Client:details.html.twig")
     *
     * @return array
     */
    public function detailsAction(Request $request, $id)
    {
        $client = $this->getRestClient()->get('v2/client/' . $id, 'Client');

        return [
            'client'      => $client,
            'namedDeputy' => $this->getNamedDeputy($id, $client)
        ];
    }

    /**
     * @Route("/{id}/discharge", name="admin_client_discharge", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param $id
     *
     * @Template("AppBundle:Admin/Client/Client:discharge.html.twig")
     *
     * @return array
     */
    public function dischargeAction(Request $request, $id)
    {
        $client = $this->getRestClient()->get('v2/client/' . $id, 'Client');

        return [
            'client' => $client,
            'namedDeputy' => $this->getNamedDeputy($id, $client)
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", name="admin_client_discharge_confirm", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dischargeConfirmAction(Request $request, $id)
    {
        $this->getRestClient()->delete('client/' . $id . '/delete');

        return $this->redirectToRoute('admin_client_search');
    }

    /**
     * @param int $id
     * @param Client $client
     * @return User|null
     */
    private function getNamedDeputy(int $id, Client $client)
    {
        $namedDeputy = null;
        if (!is_null($client->getNamedDeputy())) {
            $namedDeputy = $client->getNamedDeputy();
        } else {
            $clientWithUsers = $this->getRestClient()->get('client/' . $id . '/details', 'Client');

            foreach ($clientWithUsers->getUsers() as $user) {
                if ($user->isLayDeputy()) {
                    $namedDeputy = $clientWithUsers->getUsers()[0];
                    break;
                }
            }
        }

        return $namedDeputy;
    }
}
