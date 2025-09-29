<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DateTimeProvider $dateTimeProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'onCheckPassport',
            LoginSuccessEvent::class  => 'onLoginSuccess',
            LoginFailureEvent::class  => 'onLoginFailure',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $user = $event->getPassport()->getUser();
        if ($user instanceof UserInterface && method_exists($user, 'getId')) {
            $this->logger->warning('Login attempt', [
                'timestamp' => $this->dateTimeProvider->getDateTime()->format(DATE_ATOM),
                'user_id'   => $user->getId(),
            ]);
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof UserInterface && method_exists($user, 'getId')) {
            $this->logger->warning('Successful login', [
                'timestamp' => $this->dateTimeProvider->getDateTime()->format(DATE_ATOM),
                'user_id'   => $user->getId(),
            ]);
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof UserInterface && method_exists($user, 'getId')) {
            $this->logger->warning('Failed login', [
                'timestamp' => $this->dateTimeProvider->getDateTime()->format(DATE_ATOM),
                'user_id'   => $user->getId(),
            ]);
        } else {
            $this->logger->warning('Failed login (no matching user)', [
                'timestamp' => $this->dateTimeProvider->getDateTime()->format(DATE_ATOM),
            ]);
        }
    }
}
