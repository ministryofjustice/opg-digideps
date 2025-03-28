<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DeputyshipController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ClientApi $clientApi,
    ) {
    }

    /**
     * Show clients associated with the deputyship of the logged in user,
     * retrieved by deputy UID.
     *
     * @Route("/deputyship-details/clients", name="deputyship_details_clients")
     *
     * @Template("@App/Deputyship/client-list.html.twig")
     */
    public function clientListAction(Request $request): RedirectResponse|array
    {
        /** @var ?User $user */
        $user = $this->tokenStorage?->getToken()?->getUser();
        if (is_null($user)) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $deputyUid = $user->getDeputyUid();
        if (is_null($deputyUid)) {
            return new RedirectResponse($this->generateUrl('invalid_data'));
        }

        $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid, ['client', 'client-name']);

        return ['deputyUid' => $user->getDeputyUid(), 'clients' => $clients];
    }
}
