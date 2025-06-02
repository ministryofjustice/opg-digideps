<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use App\Service\CourtOrderService;
use App\Service\Client\Internal\DeputyApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/courtorder')]
class CourtOrderController extends AbstractController
{
    public function __construct(
        private readonly UserApi $userApi,
        private readonly CourtOrderService $courtOrderService,
        private readonly ClientApi $clientApi,
        private readonly DeputyApi $deputyApi,
    ) {}

    #[Route(path: '/deputy/{uid}', name: "courtorder_by_uid", requirements:['id' => '\d+'], methods: ['GET'])]
    #[Template("@App/CourtOrder/index.html.twig")]
    public function getOrdersByUidAction(string $uid): array
    {
        $user = $this->userApi->getUserWithData(['user-clients', 'client']);

        $courtOrder = $this->courtOrderService->getByUid($uid);

        $client = $this->clientApi->getById($courtOrder->getClient()->getId());

        return [
            'coDeputies' => $courtOrder->getCoDeputies(strval($user->getDeputyUid())),
            'courtOrder' => $courtOrder,
            'reportType' => $courtOrder->getActiveReportType(),
            'clientFullName' => $client->getFullName(),
        ];
    }

    #[Route(path: '/multi-report', name: "courtorders_reports_by_user", methods: ['GET'])]
    #[Template("@App/Index/choose-a-court-order.html.twig")]
    public function getAllDeputyCourtOrders(): array
    {   // Structure of returned data can be found in api/app/src/Repository/DeputyRepository.php
        $results = $this->deputyApi->findAllDeputyCourtOrdersForCurrentUser();

        if (empty($results)) {
            $this->redirectToRoute('homepage');
        }

        return [ 'courtOrders' => $results];
    }
}
