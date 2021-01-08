<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Form\Admin\SearchClientType;
use App\Service\Client\RestClient;
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
    /** @var RestClient */
    private $restClient;

    public function __construct(
        RestClient $restClient
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @Route("/search", name="admin_client_search")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App:Admin/Client/Search:search.html.twig")
     *
     * @param Request $request
     *
     * @return array|string
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

        $clients = $this->restClient->get('client/get-all?' . http_build_query($filters), 'Client[]');

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
