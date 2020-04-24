<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Form\Admin\SearchClientType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/client")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="admin_client_search")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Client/Search:search.html.twig")
     */
    public function searchAction(Request $request)
    {
        $searchQuery = $request->query->get('search_clients');
        $form = $this->createForm(SearchClientType::class, null, ['method' => 'GET']);


        if (null === $searchQuery) {
            return $this->buildViewParams($form);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData() + $this->getDefaultFilters($request);
        }

        $clients = $this->getRestClient()->get('client/get-all?' . http_build_query($filters), 'Client[]');

        return $this->buildViewParams($form, $clients, $filters);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $clients
     * @param array $filters
     * @return array|string
     */
    private function buildViewParams(FormInterface $form, array $clients = [], array $filters = []): array
    {
        return [
            'form' => $form->createView(),
            'clients' => $clients,
            'filters' => $filters
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getDefaultFilters(Request $request): array
    {
        return [
            'limit' => 100,
            'offset' => $request->get('offset', '0'),
            'q' => '',
            'order_by' => 'id',
            'sort_order' => 'DESC',
        ];
    }
}
