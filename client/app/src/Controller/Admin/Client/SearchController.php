<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Form\Admin\SearchClientType;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/client")
 */
class SearchController extends AbstractController
{
    /** @var RestClient */
    private $restClient;

    public function __construct(
        RestClient $restClient,
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @Route("/search", name="admin_client_search")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Client/Search/search.html.twig")
     */
    public function searchAction(Request $request): array|string
    {
        $searchQuery = $request->query->all('search_clients');
        $form = $this->createForm(SearchClientType::class, null, ['method' => 'GET']);

        if (empty($searchQuery)) {
            return $this->buildViewParams($form);
        }

        $filters = [];

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData() + $this->getDefaultFilters($request);
        }

        $clients = $this->restClient->get('client/get-all?'.http_build_query($filters), 'Client[]');

        return $this->buildViewParams($form, $clients, $filters);
    }

    /**
     * @return array|string
     */
    private function buildViewParams(FormInterface $form, array $clients = [], array $filters = []): array
    {
        return [
            'form' => $form->createView(),
            'clients' => $clients,
            'filters' => $filters,
        ];
    }

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
