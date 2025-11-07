<?php

namespace App\Service;

use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * getFirstPageAfterLogin() called after authentication
 * getHomepageRedirect() called if returning to base domain
 * Both methods have self-contained logic for prof/pa and admin landing pages
 * This logic is used to determine which page the user should be directed to depending on their status (NDR/Co-Deputy/Multi-client).
 */
class Redirector
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authChecker,
        protected RouterInterface $router,
        protected RequestStack $requestStack,
        protected string $env,
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

    private function getCorrectLayHomepage(?User $user = null): string
    {
        if (is_null($user)) {
            return $this->router->generate('login');
        }

        // checks if user has missing details
        $route = $this->getCorrectRouteIfDifferent($user, 'courtorders_for_deputy');
        if (is_string($route)) {
            return $this->router->generate($route);
        }

        return $this->router->generate('courtorders_for_deputy');
    }

    public function removeLastAccessedUrl()
    {
        $this->requestStack->getSession()->remove('_security.secured_area.target_path');
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

        // deputy: if logged, redirect to court order(s)
        if ($this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getCorrectLayHomepage($this->getLoggedUser());
        }

        return false;
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
        $coDeputySignupRoutes = [User::UNKNOWN_REGISTRATION_ROUTE, User::CO_DEPUTY_INVITE];

        // none of these corrections apply to admin
        if ($user->hasAdminRole()) {
            return false;
        }

        if ($user->getIsCoDeputy() || User::CO_DEPUTY_INVITE === $user->getRegistrationRoute()) {
            $coDeputyClientConfirmed = $user->getCoDeputyClientConfirmed();

            // already verified - shouldn't be on verification page
            if ('codep_verification' === $currentRoute && $coDeputyClientConfirmed) {
                $route = 'courtorders_for_deputy';
            }

            // unverified codeputy invitation
            if (!$coDeputyClientConfirmed && in_array($user->getRegistrationRoute(), $coDeputySignupRoutes)) {
                $route = 'codep_verification';
            }
        } elseif (!$user->isDeputyOrg()) {
            // user has no clients => 'client_add'
            if (!$user->getIdOfClientWithDetails()) {
                $clients = [];
                $deputyUid = $user->getDeputyUid();
                if (is_null($deputyUid)) {
                    $this->logger->error(
                        "Deputy with ID {$user->getId()} has NULL deputy_uid " .
                        '(via Redirector::getCorrectRouteIfDifferent)'
                    );
                } else {
                    // check if user has clients
                    $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid);
                }

                if (is_null($clients)) {
                    $this->logger->error(
                        "API call getAllClientsByDeputyUid() with deputy UID {$deputyUid} returned null " .
                        '(via Redirector::getCorrectRouteIfDifferent)'
                    );
                } elseif (0 == count($clients)) {
                    $route = 'client_add';
                }
            }

            // incomplete user info
            if (!$user->hasAddressDetails()) {
                $route = 'user_details';
            }
        }

        return (!empty($route) && $route !== $currentRoute) ? $route : false;
    }
}
