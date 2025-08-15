<?php

declare(strict_types=1);

namespace App\v2\Controller;

use App\v2\Service\CourtOrderService;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Show court order data to deputies.
 */
#[Route('/courtorder')]
class CourtOrderController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private readonly CourtOrderService $courtOrderService,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Get a court order by UID, but only if the logged-in deputy is associated with it.
     *
     * Note that reports are not returned in a guaranteed order, so the "submitted" property, or possibly null
     * submit_date and un_submit_date, should be used to determine whether the report has been submitted.
     *
     * path on API = /v2/courtorder/<UID>.
     */
    #[Route('/{uid}', requirements: ['uid' => '\w+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getByUid(string $uid): JsonResponse
    {
        $user = $this->getUser();

        $courtOrder = $this->courtOrderService->getByUidAsUser($uid, $user);

        // NB we are returning a 404 if the user does not have permission to see the court order,
        // rather than returning a 403 or similar, as the latter might reveal information about whether the court order
        // UID exists or not (a 403 would imply the resource exists but the user doesn't have permission to see it)
        if (is_null($courtOrder)) {
            return $this->buildNotFoundResponse('Could not find court order');
        }

        $ctx = SerializationContext::create()
            ->setGroups([
                'court-order-full', 'client', 'deputy', 'deputy-user', 'user', 'report', 'ndr', 'report-submission', 'status',
            ])
            ->setSerializeNull(true);

        $data = $this->serializer->serialize([
            'success' => true,
            'data' => $courtOrder,
            'code' => 200,
        ], 'json', $ctx);

        return new JsonResponse(data: $data, json: true);
    }
}
