<?php

declare(strict_types=1);

namespace App\v2\Controller;

use App\Controller\RestController;
use App\Entity\User;
use App\v2\Service\CourtOrderService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Show court order data to deputies.
 */
#[Route('/courtorder')]
class CourtOrderController extends RestController
{
    use ControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private readonly CourtOrderService $courtOrderService,
        private readonly SerializerInterface $serializer,
        private readonly TokenStorageInterface $tokenStorage,
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
    #[Security('is_granted("ROLE_DEPUTY")')]
    public function getByUidAction(string $uid): JsonResponse
    {
        $user = $this->getUser();

        $courtOrder = $this->courtOrderService->getByUidAsUser($uid, $user);

        // NB we are returning a 404 if the user does not have permission to see the court order,
        // rather than returning a 403 or similar, as the latter might reveal information about whether the court order
        // UID exists or not (a 403 would imply the resource exists but the user doesn't have permission to see it)
        if (is_null($courtOrder)) {
            return $this->buildNotFoundResponse('Could not find court order');
        }

        $ctx = SerializationContext::create()->setGroups(['court-order-full', 'client', 'deputy', 'report']);

        $data = $this->serializer->serialize([
            'success' => true,
            'data' => $courtOrder,
            'code' => 200,
        ], 'json', $ctx);

        return new JsonResponse(data: $data, json: true);
    }
}
