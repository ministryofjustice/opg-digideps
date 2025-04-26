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
        private readonly CourtOrderService $courtOrderService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @Route("/courtorder/{uid}", name="courtorder_by_uid")
     *
     * @Template("@App/CourtOrder/index.html.twig")
     */
    public function getOrdersByUidAction(string $uid): array
    {
        $data = [
            'courtOrder' => $this->courtOrderService->getByUid($uid),
        ];

        $this->logger->warning(print_r($data, true));

        return $data;
    }
}
