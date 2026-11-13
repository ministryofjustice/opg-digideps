<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Backend\Account\AccountService;
use OPG\Digideps\Backend\Account\PasswordService;
use OPG\Digideps\Common\Account\AcceptRequest;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\PasswordRequest;
use OPG\Digideps\Common\Account\ResetRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly PasswordService $passwordService,
    ) {
    }

    #[Route('/account/request', name: 'account_request', methods: 'POST')]
    public function request(Request $request): JsonResponse
    {
        return new JsonResponse($this->accountService->requestAccount(AccountRequest::jsonDeserialize($request->getContent())));
    }

    #[Route('/account/password/reset', name: 'account_password_reset', methods: 'POST')]
    public function reset(Request $request): JsonResponse
    {
        return new JsonResponse($this->accountService->requestPasswordReset(ResetRequest::jsonDeserialize($request->getContent())));
    }

    #[Route('/account/password/set', name: 'account_password_set', methods: 'POST')]
    public function set(Request $request): JsonResponse
    {
        return new JsonResponse($this->passwordService->setPasswordFromRequest(PasswordRequest::jsonDeserialize($request->getContent())));
    }

    #[Route('/account/accept', name: 'account_accept', methods: 'POST')]
    public function accept(Request $request): JsonResponse
    {
        return new JsonResponse($this->accountService->acceptTerms(AcceptRequest::jsonDeserialize($request->getContent())));
    }
}
