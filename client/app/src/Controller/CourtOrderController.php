<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\Internal\UserApi;
use App\Service\CourtOrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class CourtOrderController extends AbstractController
{
    public function __construct(
        private readonly UserApi $userApi,
        private readonly CourtOrderService $courtOrderService
    ) {
    }

    /**
     * @Route("/courtorder/{uid}", name="courtorder_by_uid")
     *
     * @Template("@App/CourtOrder/index.html.twig")
     */
    public function getOrdersByUidAction(string $uid): array
    {
        $user = $this->userApi->getUserWithData(['user-clients', 'client']);

        $courtOrder = $this->courtOrderService->getByUid($uid);

        return [
            // TODO - sort
            'coDeputies' => $courtOrder->getCoDeputies(strval($user->getDeputyUid())),
            'courtOrder' => $courtOrder,
        ];
    }
}
