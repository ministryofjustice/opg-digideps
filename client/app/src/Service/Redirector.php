<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * getFirstPageAfterLogin() called after authentication
 * getHomepageRedirect() called if returning to base domain
 * Both methods have self-contained logic for prof/pa and admin landing pages
 * For lays both will direct to getLayDeputyHomepage(), which calls to getCorrectRouteIfDifferent()
 * This logic is used to determine which page the user should be directed to depending on their status (NDR/Co-Deputy/Multi-client).
 */
class Redirector
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authChecker,
        protected RouterInterface $router,
        protected Session $session,
        protected string $env,
        private ClientApi $clientApi,
        private readonly LoggerInterface $logger,
    ) {
    }

    private function getLoggedUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (is_null($token)) {
            return null;
        }

        /* @var ?User */
        return $token->getUser();
    }

    private function getCorrectLayHomepage(?User $user): string
    {
        if (is_null($user)) {
            return $this->router->generate('login');
        }

        $deputyUid = $user->getDeputyUid();
        $clients = !is_null($deputyUid) ? $this->clientApi->getAllClientsByDeputyUid($deputyUid) : [];

        if (!is_array($clients)) {
            return $this->getLayDeputyHomepage($user);
        }

        if (count($clients) > 1) {
            // checks if user has missing details or is NDR
            $route = $this->getCorrectRouteIfDifferent($user, 'choose_a_client');
            if (is_string($route)) {
                return $this->router->generate($route);
            }

            return $this->router->generate('choose_a_client');
        } else {
            $activeClientId = count($clients) > 0 ? $clients[0]->getId() : null;

            return $this->getLayDeputyHomepage($user, $activeClientId);
        }
    }

    private function getLayDeputyHomepage(User $user, ?int $activeClientId = null): string
    {
        // checks if user has missing details or is NDR
        $route = $this->getCorrectRouteIfDifferent($user, 'lay_home');
        if (is_string($route)) {
            return $this->router->generate($route);
        }

        // redirect to create report if report is not present
        $allActiveClients = [];
        $deputyUid = $user->getDeputyUid();
        if (!is_null($deputyUid)) {
            $allActiveClients = $this->clientApi->getAllClientsByDeputyUid($deputyUid, ['client-reports', 'report']);
        }

        if (is_null($allActiveClients)) {
            $allActiveClients = [];
        }

        foreach ($allActiveClients as $activeClient) {
            if (count($activeClient->getReportIds()) >= 1) {
                break;
            }

            if (!$user->isNdrEnabled()) {
                return $this->router->generate('report_create', ['clientId' => $user->getIdOfClientWithDetails()]);
            }
        }

        // check if last remaining active client is linked to non-primary account if so retrieve id
        if (is_null($activeClientId)) {
            $activeClientId = $user->getIdOfClientWithDetails();
        }

        // TODO if $activeClientId is still null, redirect to appropriate page

        return $this->router->generate('lay_home', ['clientId' => $activeClientId]);
    }

    public function getFirstPageAfterLogin(SessionInterface $session): string
    {
        $user = $this->getLoggedUser();

        $isAdminUser = $this->authChecker->isGranted(User::ROLE_ADMIN);
        $isAdUser = $this->authChecker->isGranted(User::ROLE_AD);
        $isLayDeputy = $this->authChecker->isGranted(User::ROLE_LAY_DEPUTY);
        $isDeputyOrg = !is_null($user) && $user->isDeputyOrg();
        $inPasswordCreateContext = 'password-create' === $session->get('login-context');

        if ($inPasswordCreateContext && ($isAdminUser || $isAdUser || $isDeputyOrg)) {
            return $this->router->generate('user_details');
        }

        if ($isAdminUser) {
            return $this->router->generate('admin_homepage');
        }

        if ($isAdUser) {
            return $this->router->generate('ad_homepage');
        }

        if ($isDeputyOrg) {
            return $this->router->generate('org_dashboard');
        }

        if ($isLayDeputy) {
            return $this->getCorrectLayHomepage($user);
        }

        return $this->router->generate('access_denied');
    }

    public function getCorrectRouteIfDifferent(User $user, string $currentRoute): bool|string
    {
        // none of these corrections apply to admin
        if ($user->hasAdminRole()) {
            return $currentRoute;
        }

        $route = null;

        if ($user->getIsCoDeputy()) {
            if ($user->getCoDeputyClientConfirmed()) {
                // already verified - shouldn't be on verification page
                if ('codep_verification' == $currentRoute) {
                    $route = 'lay_home';
                }
            } elseif (User::CO_DEPUTY_INVITE == $user->getRegistrationRoute()) {
                // unverified codeputy invitation
                $route = 'codep_verification';
            }
        } elseif (!$user->isDeputyOrg()) {
            // client is not added
            if (!$user->getIdOfClientWithDetails()) {
                // Check if user has multiple clients
                $clients = [];
                $deputyUid = $user->getDeputyUid();
                if (!is_null($deputyUid)) {
                    // TODO might this make $clients null?
                    $clients = $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid());
                }

                if (is_array($clients) && 0 == count($clients)) {
                    $route = 'client_add';
                }
            }

            // incomplete user info
            if (!$user->hasAddressDetails()) {
                $route = 'user_details';
            }
        }

        if (!is_null($route) && $route !== $currentRoute) {
            return $route;
        }

        return false;
    }

    public function removeLastAccessedUrl(): void
    {
        $this->session->remove('_security.secured_area.target_path');
    }

    public function getHomepageRedirect(): bool|string
    {
        if ('admin' === $this->env) {
            // admin domain: redirect to specific admin/ad homepage, or login page (if not logged)
            if ($this->authChecker->isGranted(User::ROLE_ADMIN)) {
                return $this->router->generate('admin_homepage');
            }
            if ($this->authChecker->isGranted(User::ROLE_AD)) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        // PROF and PA redirect to org homepage
        if ($this->authChecker->isGranted(User::ROLE_ORG)) {
            return $this->router->generate('org_dashboard');
        }

        // deputy: if logged, redirect to overview pages
        if ($this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getCorrectLayHomepage($this->getLoggedUser());
        }

        return false;
    }
}
