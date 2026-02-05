<?php

declare(strict_types=1);

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MicrosoftAuthController extends AbstractController
{
    public function __construct(
        private readonly string $environment
    ) {}

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/microsoft", name="connect_microsoft_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        if ($this->environment !== 'admin') {
            return $this->redirectToRoute('login');
        }

        return $clientRegistry
            ->getClient('office365')
            ->redirect([
                'openid User.Read'
            ], []);
    }

    /**
     * After going to Microsoft, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/microsoft/check", name="connect_microsoft_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        // Handled in MicrosoftAuthenticator
    }
}
