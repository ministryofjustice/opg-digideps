<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\Client\Internal\UserApi;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserApi $userApi,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): bool
    {
        $uri = $event->getRequest()->getRequestUri();
        if (!stristr($uri, '/courtorder')) {
            return false;
        }

        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();

        // only redirect non-primary users after they have registered
        if (is_null($user) || $user->getIsPrimary()) {
            return false;
        }

        $primaryEmail = $this->userApi->returnPrimaryEmail($user->getDeputyUid());

        /** @var Session $session */
        $session = $event->getRequest()->getSession();

        $flashBag = $session->getFlashBag();

        if (is_null($primaryEmail)) {
            $flashBag->add(
                'nonPrimaryRedirectUnknownEmail',
                [
                    'sentenceOne' => 'This account has been closed.',
                    'sentenceTwo' => 'You can now access all of your reports in the same place from your primary account.',
                    'sentenceThree' => 'If you need assistance, contact your case manager on 0300 456 0300.',
                ]
            );
        } else {
            $flashBag->add(
                'nonPrimaryRedirect',
                [
                    'sentenceOne' => 'This account has been closed.',
                    'sentenceTwo' => 'You can now access all of your reports in the same place from your account under',
                    'primaryEmail' => $primaryEmail,
                ]
            );
        }

        $url = $this->urlGenerator->generate('app_logout', ['notPrimaryAccount' => true]);
        $response = new RedirectResponse($url);
        $event->setResponse($response);

        return true;
    }
}
