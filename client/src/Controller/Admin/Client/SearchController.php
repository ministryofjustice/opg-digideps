<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form\Admin\SearchClientType;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\CourtOrderApi;
use App\Service\ParameterStoreService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $clientApi;
    private $courtOrderApi;

    public function __construct(
        ClientApi $clientApi,
        CourtOrderApi $courtOrderApi
    ) {
        $this->clientApi = $clientApi;
        $this->courtOrderApi = $courtOrderApi;
    }

    /**
     * @Route("/admin/client/search", name="admin_client_search")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Client/Search/search.html.twig")
     *
     * @return array|string
     */
    public function searchAction(Request $request, ParameterStoreService $parameterStore)
    {
        $featureFlag = $parameterStore->getFeatureFlag(ParameterStoreService::FLAG_PAPER_REPORTS);

        /** @var EntityDir\User $user */
        $user = $this->getUser();

        $searchQuery = $request->query->get('search_clients');
        $form = $this->createForm(SearchClientType::class, null, ['method' => 'GET']);

        if (null === $searchQuery) {
            return $this->buildClientViewParams($form);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData() + $this->getDefaultFilters($request);
        }

        if (('1' === $featureFlag && EntityDir\User::ROLE_SUPER_ADMIN === $user->getRoleName())
            || EntityDir\User::ROLE_BEHAT_TEST === $user->getRoleName()) {
            $courtOrders = $this->courtOrderApi->searchForCourtOrders($filters);

            return $this->render(
                '@App/Admin/CourtOrder/Search/court-order-search.html.twig',
                $this->buildCourtOrderViewParams($form, $courtOrders, $filters)
            );
        } else {
            $clients = $this->clientApi->searchForClients($filters);

            return $this->buildClientViewParams($form, $clients, $filters);
        }
    }

    /**
     * @return array|string
     */
    private function buildClientViewParams(FormInterface $form, array $clients = [], array $filters = []): array
    {
        return [
            'form' => $form->createView(),
            'clients' => $clients,
            'filters' => $filters,
        ];
    }

    /**
     * @return array|string
     */
    private function buildCourtOrderViewParams(FormInterface $form, array $courtOrders = [], array $filters = []): array
    {
        return [
            'form' => $form->createView(),
            'courtOrders' => $courtOrders,
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
