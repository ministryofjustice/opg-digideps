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
 * For lays both will direct to getLayDeputyHomepage(), which calls to getCorrectRouteIfDifferent()
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
        if (!$user->hasAdminRole()) {
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
            } elseif (!$user->isDeputyOrg()) {
                // client is not added
                if (!$user->getIdOfClientWithDetails()) {
                    $clients = [];
                    $deputyUid = $user->getDeputyUid();
                    if (is_null($deputyUid)) {
                        $this->logger->error(
                            "Deputy with ID {$user->getId()} has NULL deputy_uid ".
                            '(via Redirector::getCorrectRouteIfDifferent)'
                        );
                    } else {
                        // Check if user has multiple clients
                        $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid);
                    }

                    if (is_null($clients)) {
                        $this->logger->error(
                            "API call getAllClientsByDeputyUid() with deputy UID {$deputyUid} returned null ".
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
        }

        return (!empty($route) && $route !== $currentRoute) ? $route : false;
    }

    /**
     * @return string
     */
    private function getLayDeputyHomepage(User $user, $activeClientId = null)
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'lay_home')) {
            return $this->router->generate($route);
        }

        // redirect to create report if report is not created
        $allActiveClients = [];

        $deputyUid = $user->getDeputyUid();
        if (is_null($deputyUid)) {
            $this->logger->error(
                "Deputy with ID {$user->getId()} has NULL deputy_uid ".
                '(via Redirector::getLayDeputyHomepage)'
            );
        } else {
            $allActiveClients = $this->clientApi->getAllClientsByDeputyUid($deputyUid, ['client-reports', 'report']);
        }

        if (is_null($allActiveClients)) {
            $this->logger->error(
                "API call getAllClientsByDeputyUid() with deputy UID {$deputyUid} returned null ".
                '(via Redirector::getLayDeputyHomepage)'
            );
            $allActiveClients = [];
        }

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

        if (is_null($activeClientId)) {
            $activeClientId = $user->getIdOfClientWithDetails();

            if (is_null($activeClientId)) {
                $this->logger->error(
                    "Unable to get client ID for user with ID {$user->getId()}; ".
                    'getIdOfClientWithDetails() returned a null value'
                );

                return $this->router->generate('invalid_data');
            }
        }

        return $this->router->generate('lay_home', ['clientId' => $activeClientId]);
    }

    public function removeLastAccessedUrl()
    {
        $this->requestStack->getSession()->remove('_security.secured_area.target_path');
    }

    /**
     * @return string
     */
    public function getHomepageRedirect()
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

    /**
     * @return string
     */
    private function getChooseAClientHomepage(User $user)
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'choose_a_client')) {
            return $this->router->generate($route);
        }

        return $this->router->generate('choose_a_client');
    }

    private function getCorrectLayHomepage(?User $user = null)
    {
        if (is_null($user)) {
            return $this->router->generate('login');
        }

        $clients = [];
        $deputyUid = $user->getDeputyUid();
        if (is_null($deputyUid)) {
            $this->logger->error(
                "Deputy with ID {$user->getId()} has NULL deputy_uid ".
                '(via Redirector::getCorrectLayHomepage)'
            );
        } else {
            $clients = $this->clientApi->getAllClientsByDeputyUid($deputyUid);
            if (is_null($clients)) {
                $this->logger->error(
                    "API call getAllClientsByDeputyUid() with deputy UID {$deputyUid} returned null ".
                    '(via Redirector::getCorrectLayHomepage)'
                );
                $clients = [];
            } else {
                $clients = array_values($clients);
            }
        }

        $numClients = count($clients);

        if (0 === $numClients) {
            return $this->getLayDeputyHomepage($user);
        } elseif (1 === $numClients) {
            $activeClientId = $clients[0]->getId();

            return $this->getLayDeputyHomepage($user, $activeClientId);
        }

        return $this->getChooseAClientHomepage($user);
    }
}
