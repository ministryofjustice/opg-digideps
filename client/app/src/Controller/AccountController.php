<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Common\Account\AcceptRequest;
use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\MaximumTokenAge;
use OPG\Digideps\Common\Account\NonEmptyString;
use OPG\Digideps\Common\Account\Password;
use OPG\Digideps\Common\Account\PasswordRequest;
use OPG\Digideps\Common\Account\ResetRequest;
use OPG\Digideps\Common\Account\Token;
use OPG\Digideps\Common\Account\ValidationKey;
use OPG\Digideps\Frontend\Account\AccountService;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Form\Account\AcceptRequestType;
use OPG\Digideps\Frontend\Form\Account\AccountRequestType;
use OPG\Digideps\Frontend\Form\Account\PasswordRequestType;
use OPG\Digideps\Frontend\Form\Account\ResetRequestType;
use OPG\Digideps\Frontend\Validator\RouteValidator;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
    ) {
    }

    #[Route('/account/request', name: 'account_request', methods: ['GET', 'POST'])]
    #[Template('@App/Account/request.html.twig')]
    public function requestAccountAction(Request $request): array
    {
        $form = $this->createRenderableForm(AccountRequestType::class, $this->generateUrl('account_request'));

        $accountCreated = false;
        $deputyEmail = null;

        // handle submitted form, checking data against db tables and associating user with deputy,
        // or showing verification failure message (could be wrong data from user, could be data missing from db if
        // ingest hasn't happened yet)
        if ($form->handleRequest($request)) {
            $accountRequest = new AccountRequest(
                new NonEmptyString(strtolower($form->getStringOrThrow('caseNumber'))),
                new NonEmptyString($form->getStringOrThrow('deputyFirstName')),
                new NonEmptyString($form->getStringOrThrow('deputyLastName')),
                new NonEmptyString($form->getStringOrThrow('deputyPostCode')),
                new Email(strtolower($form->getStringOrThrow('deputyEmail'))),
                new NonEmptyString($form->getStringOrThrow('clientLastName'))
            );

            $response = $this->accountService->sendAccountRequest($accountRequest);

            if ($response instanceof Email) {
                $accountCreated = true;
                $deputyEmail = $response;
            } elseif ($response instanceof AccountError) {
                // add a form-level error message
                $formErrorMessage = $this->translator->trans(
                    "opg.account.request.errors.{$response->value}",
                    ['%helplineNumber%' => $this->translator->trans('opg.helplineNumber', [], 'twig-components')],
                    'twig-components'
                );

                $form->addError(new FormError($formErrorMessage));
            }
        }

        return [
            'form' => $form->render(),
            'accountCreated' => $accountCreated,
            'deputyEmail' => $deputyEmail
        ];
    }

    #[Route('/account/forgotten', name: 'account_forgotten', methods: ['GET', 'POST'])]
    #[Template('@App/Account/reset.html.twig')]
    public function requestPasswordAction(Request $request): array
    {
        $form = $this->createRenderableForm(ResetRequestType::class, $this->generateUrl('account_forgotten'));
        $form->handleRequest($request);

        if ($form->handleRequest($request)) {
            $email = new Email(strtolower($form->getStringOrThrow('email')));
            $result = $this->accountService->sendResetRequest(new ResetRequest($email));
            if ($result instanceof Email) {
                $this->addFlash('notice', "Send email to {$result->value}.");
            } else {
                $this->addFlash('error', "{$result->value}");
            }
            $form = $form->recreate();
        }

        return ['form' => $form->render()];
    }

    #[Route('/account/activate/{key}/{token}', name: 'account_activate', methods: ['GET', 'POST'])]
    #[Template('@App/Account/password.html.twig')]
    public function activateAction(Request $request, string $key, string $token): array|RedirectResponse
    {
        return $this->processPasswordRequest($request, 'account_activate', 'account_request', $key, $token);
    }

    #[Route('/account/reset/{key}/{token}', name: 'account_reset', methods: ['GET', 'POST'])]
    #[Template('@App/Account/password.html.twig')]
    public function passwordResetAction(Request $request, string $key, string $token): array|RedirectResponse
    {
        return $this->processPasswordRequest($request, 'account_reset', 'account_password_forgotten', $key, $token);
    }

    private function processPasswordRequest(Request $request, string $actionRoute, string $errorRoute, string $key, string $token): array|RedirectResponse
    {
        if (!$this->validateToken($key, $token)) {
            return new RedirectResponse($this->generateUrl($errorRoute));
        }

        $form = $this->createRenderableForm(PasswordRequestType::class, $this->generateUrl($actionRoute, ['key' => $key, 'token' => $token]));
        if ($form->handleRequest($request)) {
            $result = $this->accountService->sendPasswordRequest(new PasswordRequest(
                new Password($form->getStringOrThrow('password')),
                new Token($token),
                new ValidationKey($key)
            ));
            if ($result instanceof AccountError) {
                $this->addFlash('error', $result->value);
                return new RedirectResponse($this->generateUrl($errorRoute));
            } else {
                return new RedirectResponse($this->generateUrl('login'));
            }
        }

        return ['form' => $form->render(),];
    }

    private function validateToken(string $key, string $token): bool
    {
        if (!ValidationKey::isValid($key) || !Token::isValid($token)) {
            $this->addFlash('error', UuidV7::v7());
            return false;
        }
        if (new Token($token)->age > MaximumTokenAge::PasswordReset->value) {
            $this->addFlash('error', '');
            return false;
        }

        return true;
    }

    #[Route('/account/accept', name: 'account_accept', methods: ['GET', 'POST'])]
    #[Template('@App/Account/accept.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function acceptAction(Request $request): array|RedirectResponse
    {
        $page = $request->query->getString('page');
        $user = $this->getUser();
        $userId = $user instanceof User ? $user->getId() : null;
        if ($userId === null) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $form = $this->createRenderableForm(AcceptRequestType::class, $this->generateUrl('account_accept', ['page' => $page]));

        if ($form->handleRequest($request)) {
            $response = $this->accountService->sendAcceptRequest(new AcceptRequest($userId));
            if ($response === null) {
                $context = new RequestContext();
                $context->fromRequest($request);
                $context->setMethod(Request::METHOD_GET);

                if (!RouteValidator::validateRoute($this->router, $page, $context)) {
                    $page = $this->generateUrl('homepage');
                }
                return new RedirectResponse($page);
            } else {
                $this->addFlash('error', $response->value);
            }
        }

        return ['form' => $form->render()];
    }
}
