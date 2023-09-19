<?php

// namespace App\EventListener;
//
// use App\Event\IncorrectPasswordRequestEvent;
// use App\Event\UserPasswordResetEvent;
// use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
// use Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\RateLimiter\RateLimiterFactory;
// use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
//
// class IncorrectPasswordResetAttemptListener implements EventSubscriberInterface
// {

//    private $anonymousApiLimiter;
// //    private $request;
//
//    public function __construct(RequestRateLimiterInterface $anonymousApiLimiter)
//    {
// //       $this->request = $request;
//       $this->anonymousApiLimiter = $anonymousApiLimiter;
//    }
//
//    public static function getSubscribedEvents()
//    {
//        return [
//            IncorrectPasswordRequestEvent::class => ['checkIncorrectPasswordRequestEvent']
//        ];
//    }
//
//    public function checkIncorrectPasswordRequestEvent(IncorrectPasswordRequestEvent $event)
//    {
//        $request = $event->getIncorrectPasswordResetSession();
//
//        $limit = $this->anonymousApiLimiter->consume($request);
//
//        if (false === $limit->isAccepted()) {
//            throw new TooManyLoginAttemptsAuthenticationException(ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60));
//        }
//    }
//
//    public function onSuccessfulPasswordReset(UserPasswordResetEvent $event): void
//    {
//        //need to identify the correct parameter
//        $this->anonymousApiLimiter->reset($request);
//    }

// }
