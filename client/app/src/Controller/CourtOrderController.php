<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\CoDeputyInviteType;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\DeputyApi;
use App\Service\CourtOrderService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/courtorder')]
class CourtOrderController extends AbstractController
{
    public function __construct(
        private readonly CourtOrderService $courtOrderService,
        private readonly ClientApi $clientApi,
        private readonly DeputyApi $deputyApi,
    ) {
    }

    /**
     * Show the waiting message.
     */
    #[Route(path: '/waiting', name: 'courtorders_waiting', methods: ['GET'])]
    #[Template('@App/Index/account-setup-in-progress.html.twig')]
    public function wait(): array
    {
        return [];
    }

    /**
     * Get a court order by its UID.
     */
    #[Route(path: '/{courtOrderUid}', name: 'courtorder_by_uid', requirements: ['courtOrderUid' => '\d+'], methods: ['GET'])]
    #[Template('@App/CourtOrder/index.html.twig')]
    public function getOrderByUid(string $courtOrderUid): array
    {
        $courtOrder = $this->courtOrderService->getByUid($courtOrderUid);
        /** @var Client $client */
        $client = $this->clientApi->getById($courtOrder->getClient()->getId());

        $templateValues = [
            'coDeputies' => $courtOrder->getCoDeputies(),
            'courtOrder' => $courtOrder,
            'reportType' => $courtOrder->getActiveReportType(),
            'client' => $client,
            'inviteUrl' => $this->generateUrl('courtorder_invite', ['courtOrderUid' => $courtOrder->getCourtOrderUid()]),
            'ndrEnabled' => true,
        ];

        if (is_null($courtOrder->getNdr())) {
            $templateValues['ndrEnabled'] = false;
        }

        return $templateValues;
    }

    /**
     * Show court orders and reports for the currently-logged in deputy.
     * Redirects if no court orders or a single court order.
     */
    #[Route(path: '/choose-a-court-order', name: 'courtorders_for_deputy', methods: ['GET'])]
    #[Template('@App/Index/choose-a-court-order.html.twig')]
    public function getAllDeputyCourtOrders(): Response|array
    {
        // structure of returned data can be found in api/app/src/Service/DeputyService.php, findReportsInfoByUid()
        $results = $this->deputyApi->findAllDeputyCourtOrdersForCurrentDeputy();

        if (is_null($results) || 0 === count($results)) {
            return $this->redirectToRoute('courtorders_waiting');
        }

        if (1 === count($results)) {
            return $this->redirectToRoute('courtorder_by_uid', ['courtOrderUid' => $results[0]['courtOrderLink']]);
        }

        return ['deputyships' => $results];
    }

    /**
     * Invite or re-invite a co-deputy to collaborate on a court order. They must exist in the pre_registration table
     * for the invite to be sent successfully.
     */
    #[Route(path: '/{courtOrderUid}/invite', name: 'courtorder_invite', requirements: ['courtOrderUid' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('@App/CourtOrder/invite.html.twig')]
    public function inviteLayDeputy(Request $request, string $courtOrderUid): array|RedirectResponse
    {
        $thisPageLink = $this->generateUrl('courtorder_by_uid', ['courtOrderUid' => $courtOrderUid]);

        $invitedUser = new User();
        $form = $this->createForm(CoDeputyInviteType::class, $invitedUser);
        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            // get the client for the court order so we can retrieve their firstname
            $courtOrder = $this->courtOrderService->getByUid($courtOrderUid);
            $client = $courtOrder->getClient();

            return [
                'form' => $form->createView(),
                'clientFirstName' => $client->getFirstName(),
                'backLink' => $thisPageLink,
            ];
        }

        /** @var User $invitingUser */
        $invitingUser = $this->getUser();

        $result = $this->courtOrderService->inviteLayDeputy($courtOrderUid, $invitedUser, $invitingUser);

        /** @var Session $session */
        $session = $request->getSession();

        if (!$result->success) {
            $session->getFlashBag()->add('error', 'Invitation to deputy could not be sent');

            return new RedirectResponse($thisPageLink);
        }

        $session->getFlashBag()->add('notice', 'Deputy invitation has been sent');

        return new RedirectResponse($thisPageLink);
    }
}
