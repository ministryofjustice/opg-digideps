<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form\Admin\SearchClientType;
use App\Service\Client\RestClient;
use App\Service\ParameterStoreService;
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
        RestClient $restClient
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @Route("/search", name="admin_client_search")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Client/Search/search.html.twig")
     *
     * @return array|string
     */
    public function searchAction(Request $request, ParameterStoreService $parameterStore)
    {
        var_dump('Landing');
        //$featureFlag = $parameterStore->getFeatureFlag(ParameterStoreService::FLAG_PAPER_REPORTS);

        $featureFlag = '1';

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

        if ('1' === $featureFlag && EntityDir\User::ROLE_SUPER_ADMIN === $user->getRoleName()) {
            var_dump('Feature');

            //var_dump($filters);
            $cases = $this->restClient->get('case/search-all?'.http_build_query($filters), 'array');
            var_dump('Searched');

            var_dump($cases);

            return $this->render(
                '@App/Admin/Client/Search/case-search.html.twig',
                $this->buildCaseViewParams($form, $cases, $filters)
            );
        } else {
            var_dump('Original');
            $clients = $this->restClient->get('client/get-all?'.http_build_query($filters), 'Client[]');

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
    private function buildCaseViewParams(FormInterface $form, array $cases = [], array $filters = []): array
    {
        return [
            'form' => $form->createView(),
            'cases' => $cases,
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
