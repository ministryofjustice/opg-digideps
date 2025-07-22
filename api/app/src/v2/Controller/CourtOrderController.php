<?php

declare(strict_types=1);

namespace App\v2\Controller;

use App\Entity\User;
use App\v2\DTO\InviteeDTO;
use App\v2\Service\CourtOrderInviteService;
use App\v2\Service\CourtOrderService;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Show court order data to deputies.
 */
#[Route('/courtorder')]
class CourtOrderController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private readonly CourtOrderService $courtOrderService,
        private readonly CourtOrderInviteService $courtOrderInviteService,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Get a court order by UID, but only if the logged-in deputy is associated with it.
     *
     * Note that reports are not returned in a guaranteed order, so the "submitted" property, or possibly null
     * submit_date and un_submit_date, should be used to determine whether the report has been submitted.
     *
     * path on API = /v2/courtorder/<UID>
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

        $ctx = SerializationContext::create()
            ->setGroups([
                'court-order-full', 'client', 'deputy', 'report', 'ndr', 'report-submission',
            ])
            ->setSerializeNull(true);

        $data = $this->serializer->serialize([
            'success' => true,
            'data' => $courtOrder,
            'code' => 200,
        ], 'json', $ctx);

        return new JsonResponse(data: $data, json: true);
    }

    /**
     * Invite a co-deputy to a court order.
     *
     * path on API = /v2/courtorder/<UID>/invite
     */
    #[Route('/{uid}/invite', requirements: ['uid' => '\w+'], methods: ['POST'])]
    #[Security('is_granted("ROLE_DEPUTY")')]
    public function inviteDeputyAction(Request $request, string $uid): JsonResponse
    {
        /** @var ?array $data */
        $data = json_decode($request->getContent(), true);

        if (is_null($data)) {
            return $this->buildErrorResponse('Invalid request data', status: Response::HTTP_NOT_ACCEPTABLE);
        }

        $inviteeDTO = new InviteeDTO(
            $data['email'] ?? '',
            $data['firstname'] ?? '',
            $data['lastname'] ?? '',
            $data['role_name'] ?? User::ROLE_LAY_DEPUTY,
        );

        if (!$inviteeDTO->isValid()) {
            return $this->buildErrorResponse('invalid invitee details', status: Response::HTTP_NOT_ACCEPTABLE);
        }

        /** @var ?User $user */
        $user = $this->getUser();

        if (is_null($user)) {
            return $this->buildErrorResponse('invalid inviting user', Response::HTTP_FORBIDDEN);
        }

        $success = $this->courtOrderInviteService->invite($uid, $user, $inviteeDTO);

        return new JsonResponse(data: ['invitedSuccessfully' => $success], json: true);
    }
}
