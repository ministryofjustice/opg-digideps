<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/client")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="admin_client_search")
     * @Template
     */
    public function searchAction(Request $request)
    {
        $filters = [
            'limit'       => 100,
            'offset'      => $request->get('offset', '0'),
            'q'           => '',
            'order_by'    => 'id',
            'sort_order'  => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchClientType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $filters = $form->getData() + $filters;
        }

        $clients = $this->getRestClient()->get('client/get-all?' . http_build_query($filters), 'Client[]');

        return [
            'form'    => $form->createView(),
            'clients'   => $clients,
            'filters' => $filters,
        ];
    }
}
