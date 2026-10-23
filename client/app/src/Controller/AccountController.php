<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\NonEmptyString;
use OPG\Digideps\Common\Validating\ValidatingForm;
use OPG\Digideps\Frontend\Account\AccountService;
use OPG\Digideps\Frontend\Form\AccountRequestType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/account/request', name: 'account_request', methods: ['GET', 'POST'])]
    #[Template('@App/Account/request.html.twig')]
    public function requestAction(Request $request): array
    {
        $form = $this->createForm(AccountRequestType::class)->handleRequest($request);

        $accountCreationError = null;
        $accountCreated = false;
        $deputyEmail = null;

        // handle submitted form, checking data against db tables and associating user with deputy,
        // or showing verification failure message (could be wrong data from user, could be data missing from db if
        // ingest hasn't happened yet)
        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $accountRequest = new AccountRequest(
                new NonEmptyString(strtolower($validatingForm->getStringOrThrow('caseNumber'))),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyFirstName')),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyLastName')),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyPostCode')),
                new Email(strtolower($validatingForm->getStringOrThrow('deputyEmail'))),
                new NonEmptyString($validatingForm->getStringOrThrow('clientLastName'))
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
            'form' => $form->createView(),
            'accountCreated' => $accountCreated,
            'deputyEmail' => $deputyEmail
        ];
    }
}
