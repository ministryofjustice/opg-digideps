<?php

declare(strict_types=1);

namespace App\v2\Controller;

use App\Controller\RestController;
use App\v2\Service\CourtOrderService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/courtorder')]
class CourtOrderController extends RestController
{
    use ControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private readonly CourtOrderService $courtOrderService,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($em);
    }

    /**
     * Get a court order by UID, but only if the logged-in deputy is associated with it.
     *
     * path on API = /v2/courtorder/<UID>.
     */
    #[Route('/{uid}', requirements: ['uid' => '\w+'], methods: ['GET'])]
    // #[Security('is_granted("ROLE_DEPUTY")')]
    public function getByUidAction(string $uid): JsonResponse
    {
        $user = $this->getUser();
        $courtOrder = $this->courtOrderService->getByUidAsUser($uid, $user);

        if (is_null($courtOrder)) {
            return $this->buildNotFoundResponse(message: 'Could not find court order');
        }

        $ctx = SerializationContext::create()->setGroups(
            ['court-order-full', 'client', 'deputy', 'report']
        );
        $body = $this->serializer->serialize($courtOrder, 'json', $ctx);

        return new JsonResponse(data: $body, json: true);
    }
}
