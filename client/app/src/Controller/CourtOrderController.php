<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CourtOrder;
use App\Service\Client\RestClient;
use App\Service\CourtOrderService;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class CourtOrderController extends AbstractController
{
    public function __construct(
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
        return [
            'courtOrder' => $this->courtOrderService->getByUid($uid),
        ];
    }
}
