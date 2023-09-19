<?php

namespace App\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class IncorrectPasswordRequestEvent extends Event
{
    public const NAME = 'incorrect.passwordReset';

    public function __construct(Request $userSession)
    {
        $this->userSession = $userSession;
    }

    public function getIncorrectPasswordResetSession(): Request
    {
        return $this->userSession;
    }

    public function setIncorrectPasswordResetSession(Request $userSession): IncorrectPasswordRequestEvent
    {
        $this->userSession = $userSession;

        return $this;
    }
}
