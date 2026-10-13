<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Backend\Account\AccountService;
use OPG\Digideps\Backend\Account\PasswordService;
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
        return new JsonResponse($this->accountService->requestAccount($this->decode(AccountRequest::class, $request->getContent())));
    }

    #[Route('/account/password/reset', name: 'account_password_reset', methods: 'POST')]
    public function reset(Request $request): JsonResponse
    {
        return new JsonResponse($this->accountService->requestPasswordReset($this->decode(ResetRequest::class, $request->getContent())));
    }

    #[Route('/account/password/set', name: 'account_password_set', methods: 'POST')]
    public function set(Request $request): JsonResponse
    {
        return new JsonResponse($this->passwordService->setPasswordFromRequest($this->decode(PasswordRequest::class, $request->getContent())));
    }

    /**
     * @template T of AccountRequest|ResetRequest|PasswordRequest
     * @param class-string<T> $class
     * @return T
     */
    private function decode(string $class, string $json): AccountRequest|ResetRequest|PasswordRequest
    {
        return match ($class) {
            AccountRequest::class => AccountRequest::jsonDeserialize($json),
            ResetRequest::class => ResetRequest::jsonDeserialize($json),
            PasswordRequest::class => PasswordRequest::jsonDeserialize($json)
        };
    }
}
