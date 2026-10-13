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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class AccountController extends SymfonyAbstractController
{
    public function __construct(
        private readonly AccountService $accountService
    ) {
    }

    #[Route('/account/request', name: 'account_request', methods: ['GET', 'POST'])]
    public function requestAction(Request $request): Response|array
    {
        $form = $this->createForm(AccountRequestType::class)->handleRequest($request);

        $accountCreationError = null;
        $accountCreated = false;
        $deputyEmail = null;
        $responseCode = Response::HTTP_OK;

        // handle submitted form, checking data against db tables and associating user with deputy,
        // or showing verification failure message (could be wrong data from user, could be data missing from db if
        // ingest hasn't happened yet)
        if ($form->isSubmitted() && $form->isValid()) {
            $validatingForm = new ValidatingForm($form);
            $accountRequest = new AccountRequest(
                new NonEmptyString($validatingForm->getStringOrThrow('caseNumber')),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyFirstName')),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyLastName')),
                new NonEmptyString($validatingForm->getStringOrThrow('deputyPostCode')),
                new Email($validatingForm->getStringOrThrow('deputyEmail')),
                new NonEmptyString($validatingForm->getStringOrThrow('clientLastName'))
            );

            // TODO remove when we have the proper sendAccountRequest() method in place
            $mockResponseToReceive = $validatingForm->getStringOrThrow('mockResponseToReceive');

            // perhaps do a better job of handling errors in the RestClient, so
            // we don't need a wide \Exception catch here? or maybe do it in AccountService?
            try {
                // TODO replace with call to sendAccountRequest()
                $response = $this->accountService->sendAccountRequestMock($accountRequest, $mockResponseToReceive);

                if ($response instanceof Email) {
                    $accountCreated = true;
                    $deputyEmail = $response;
                } elseif ($response instanceof AccountError) {
                    $accountCreationError = $response;
                }
            } catch (\Exception) {
                //TODO
                $accountCreationError = null;
            }
        }

        return $this->render('@App/Registration/Account/request.html.twig', [
            'form' => $form->createView(),
            'accountCreationError' => $accountCreationError,
            'accountCreated' => $accountCreated,
            'deputyEmail' => $deputyEmail
        ], new Response(null, $responseCode));
    }
}
