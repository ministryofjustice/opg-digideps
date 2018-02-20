<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/{id}/details", name="admin_client_details", requirements={"id":"\d+"})
     *
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function detailsAction(Request $request, $id)
    {
        $client = $this->getRestClient()->get('client/' . $id . '/details', 'Client');

        return [
            'client'   => $client,
        ];
    }
}
