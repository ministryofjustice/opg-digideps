<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\UserApi;
use App\Service\CourtOrderService;
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
    ) {
    }

    /**
     * Get a court order by its UID.
     *
     * @param string $uid Court order UID
     *
     * @return array Court orders and associated data
     */
    #[Route(path: '/{uid}', name: 'courtorder_by_uid', requirements: ['uid' => '\d+'], methods: ['GET'])]
    #[Template('@App/CourtOrder/index.html.twig')]
    public function getOrderByUidAction(string $uid): array
    {   /** @var User $user */
        $user = $this->userApi->getUserWithData(['user-clients', 'client']);

        /** @var CourtOrder $courtOrder */
        $courtOrder = $this->courtOrderService->getByUid($uid);

        /** @var Client $client */
        $client = $this->clientApi->getById($courtOrder->getClient()->getId());

        $templateValues = [
            'clientHasCoDeputies' => !empty($client->getCoDeputies()),
            'coDeputies' => $courtOrder->getCoDeputies(strval($user->getDeputyUid())),
            'courtOrder' => $courtOrder,
            'reportType' => $courtOrder->getActiveReportType(),
            'client' => $client,
        ];

        if (!empty($courtOrder->getNdr())) {
            return array_merge($templateValues, [
                'ndrEnabled' => true,
            ]);
        }

        return array_merge($templateValues, [
            'ndrEnabled' => false,
        ]);
    }

    /**
     * Get all court orders and reports for the currently-logged in user.
     *
     * @return array List of court orders
     */
    #[Route(path: '/multi-report', name: 'courtorders_reports_by_user', methods: ['GET'])]
    #[Template('@App/Index/choose-a-court-order.html.twig')]
    public function getAllDeputyCourtOrders(): array
    {   // Structure of returned data can be found in api/app/src/Repository/DeputyRepository.php
        $results = $this->deputyApi->findAllDeputyCourtOrdersForCurrentUser();

        return ['courtOrders' => $results];
    }
}
