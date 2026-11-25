<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Common\Mail\NotifyMail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/notify')]
final class NotifyController extends AbstractController
{
    public function __construct(private readonly MailSenderInterface $mailSender)
    {
    }

    #[Route('/send', methods: ['POST'])]
    public function notifySend(Request $request): Response
    {
        $mail = NotifyMail::jsonDeserialize((string)$request->getContent());
        $ok = $this->mailSender->send($mail);
        return new Response($ok ? 'ok' : 'error', $ok ? 200 : 500);
    }
}
