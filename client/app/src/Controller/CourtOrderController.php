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

    #[Route(path: '/{uid}', methods: ['GET'], name: "courtorder_by_uid")]
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

    #[Route(path: '/deputy/test', name: "courtorder_reports_by_user")]
    #[Template("@App/Index/choose-a-court-order.html.twig")]
    public function getAllDeputyCourtOrders()
    {
        $results = $this->deputyApi->findAllDeputyReportsForCurrentUser();

        if (count($results) === 0 || is_null($results)) {
            return [ 'courtOrders' => [] ];
        }

        return [ 'courtOrders' => $results];
    }
}
