<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
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

        $namedDeputy = null;
        if (!is_null($client->getNamedDeputy())) {
            $namedDeputy = $this->getRestClient()->get('user/' . $client->getNamedDeputy()->getId(), 'User');
        } else {
            $clientWithUsers = $this->getRestClient()->get('client/' . $id . '/details', 'Client');

            foreach ($clientWithUsers->getUsers() as $user) {
                if ($user->isLayDeputy()) {
                    $namedDeputy = $clientWithUsers->getUsers()[0];
                    break;
                }
            }
        }

        return [
            'client'      => $client,
            'namedDeputy' => $namedDeputy,
        ];
    }
}
