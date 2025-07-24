<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\CoDeputyInviteType;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\Client\Internal\UserApi;
use App\Service\CourtOrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return array Court order and associated data
     */
    #[Route(path: '/{uid}', name: 'courtorder_by_uid', requirements: ['uid' => '\d+'], methods: ['GET'])]
    #[Template('@App/CourtOrder/index.html.twig')]
    public function getOrderByUid(string $uid): array
    {
        $courtOrder = $this->courtOrderService->getByUid($uid);

        /** @var Client $client */
        $client = $this->clientApi->getById($courtOrder->getClient()->getId());

        $templateValues = [
            'courtOrderHasMultipleDeputies' => count($courtOrder->getCoDeputies()) > 1,
            'coDeputies' => $courtOrder->getCoDeputies(),
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
     * Show all court orders and reports for the currently-logged in deputy.
     *
     * @return array|Response List of court orders or message if there are none available yet
     */
    #[Route(path: '/choose-a-court-order', name: 'courtorders_for_deputy', methods: ['GET'])]
    #[Template('@App/Index/choose-a-court-order.html.twig')]
    public function getAllDeputyCourtOrders(): array|Response
    {   // Structure of returned data can be found in api/app/src/Repository/DeputyRepository.php
        $results = $this->deputyApi->findAllDeputyCourtOrdersForCurrentDeputy();

        if (is_null($results) || 0 === count($results)) {
            return $this->render('@App/Index/account-setup-in-progress.html.twig');
        }

        return ['courtOrders' => $results];
    }

    /**
     * Invite or re-invite a co-deputy to collaborate on a court order. They must exist in the pre_registration table
     * for the invite to be sent successfully.
     */
    #[Route(path: '/{uid}/invite', name: 'courtorder_invite', requirements: ['uid' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('@App/CourtOrder/invite.html.twig')]
    public function inviteLayDeputy(Request $request, string $uid): array|RedirectResponse
    {
        $invitedUser = new User();
        $form = $this->createForm(CoDeputyInviteType::class, $invitedUser);
        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return ['form' => $form->createView()];
        }

        /** @var User $invitingUser */
        $invitingUser = $this->getUser();

        // TODO check response success/error codes etc. and add custom error messages to form if necessary
        $this->courtOrderService->inviteLayDeputy($uid, $invitedUser, $invitingUser);

        $request->getSession()->getFlashBag()->add('notice', 'Deputy invitation has been sent');

        return new RedirectResponse($this->generateUrl('courtorder_by_uid', ['uid' => $uid]));
    }
}
