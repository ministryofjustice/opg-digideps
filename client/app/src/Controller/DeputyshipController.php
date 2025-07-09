<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function clientListAction(): array|RedirectResponse
    {
        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();
        if (is_null($user)) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $deputyUid = $user->getDeputyUid();
        if (is_null($deputyUid)) {
            return new RedirectResponse($this->generateUrl('invalid_data'));
        }

        /** @var ?Client[] $clients */
        $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid, ['client', 'client-name']);
        if (is_null($clients)) {
            $clients = [];
        }

        $numClients = count($clients);

        if (1 === $numClients) {
            /** @var Client $client */
            $client = reset($clients);

            return new RedirectResponse($this->generateUrl('client_show', ['clientId' => $client->getId()]));
        }

        if ($numClients > 1) {
            usort($clients, function ($client1, $client2) {
                return strnatcmp($client1->getFirstName(), $client2->getFirstName());
            });
        }

        return ['clients' => $clients];
    }
}
