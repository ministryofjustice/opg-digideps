<?php

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
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly AuthorizationCheckerInterface $authChecker,
        protected readonly RouterInterface $router,
        protected readonly Session $session,
        protected readonly string $env,
        private readonly ClientApi $clientApi,
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

    private function getActiveClients(User $user): ?array
    {
        $deputyUid = $user->getDeputyUid();

        if (is_null($deputyUid)) {
            $this->logger->error("User with ID {$user->getId()} has a null deputy UID");

            return null;
        }

        $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid);

        if (is_null($clients)) {
            $this->logger->error(
                "getAllClientsByDeputyUid() with deputy UID {$deputyUid} returned null; ".
                'deputy UID may not exist in the clients table'
            );

            return null;
        }

        return array_values($clients);
    }

    private function getLayDeputyHomepage(User $user, array $allActiveClients, $activeClientId = null): string
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'lay_home')) {
            return $this->router->generate($route);
        }

        // redirect to create report if report is not created
        foreach ($allActiveClients as $activeClient) {
            if (count($activeClient->getReportIds()) >= 1) {
                break;
            }

            if (!$user->isNdrEnabled()) {
                $clientId = $user->getIdOfClientWithDetails();
                if (is_null($clientId)) {
                    $this->logger->error(
                        "Unable to get client ID for user with ID {$user->getId()}; ".
                        'getIdOfClientWithDetails() returned a null value'
                    );

                    // attempt to rectify failed client ID fetch by using the active client's ID instead
                    $clientId = $activeClient->getId();
                }

                return $this->router->generate('report_create', ['clientId' => $clientId]);
            }
        }

        // check if last remaining active client is linked to non-primary account if so retrieve id
        return is_null($activeClientId) ? $this->router->generate('lay_home', ['clientId' => $user->getIdOfClientWithDetails()]) :
            $this->router->generate('lay_home', ['clientId' => $activeClientId]);
    }

    private function getChooseAClientHomepage(User $user): string
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'choose_a_client')) {
            return $this->router->generate($route);
        }

        return $this->router->generate('choose_a_client');
    }

    private function getCorrectLayHomepage(?User $user = null): string
    {
        if (is_null($user)) {
            return $this->router->generate('login');
        }

        $clients = $this->getActiveClients($user);
        if (is_null($clients)) {
            $clients = [];
        }

        if (count($clients) > 1) {
            return $this->getChooseAClientHomepage($user);
        }

        $activeClientId = 1 === count($clients) ? $clients[0]->getId() : null;

        return $this->getLayDeputyHomepage($user, $clients, $activeClientId);
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

    public function getCorrectRouteIfDifferent(User $user, ?string $currentRoute = null): bool|string
    {
        // none of these corrections apply to admin
        if ($user->hasAdminRole()) {
            return false;
        }

        if ($user->getIsCoDeputy()) {
            $coDeputyClientConfirmed = $user->getCoDeputyClientConfirmed();

            // already verified - shouldn't be on verification page
            if ('codep_verification' == $currentRoute && $coDeputyClientConfirmed) {
                $route = 'lay_home';
            }

            // unverified codeputy invitation
            if (!$coDeputyClientConfirmed && User::CO_DEPUTY_INVITE == $user->getRegistrationRoute()) {
                $route = 'codep_verification';
            }
        } else {
            if (!$user->isDeputyOrg()) {
                if (!$user->getIdOfClientWithDetails()) {
                    $clients = $this->getActiveClients($user);

                    if (is_null($clients)) {
                        $route = 'invalid_data';
                    }

                    // client is not added
                    if (is_array($clients) && 0 == count($clients)) {
                        $route = 'client_add';
                    }
                }

                // incomplete user info
                if (!$user->hasAddressDetails()) {
                    $route = 'user_details';
                }
            }
        }

        return (!empty($route) && $route !== $currentRoute) ? $route : false;
    }

    public function removeLastAccessedUrl()
    {
        $this->session->remove('_security.secured_area.target_path');
    }

    public function getHomepageRedirect(): string
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
