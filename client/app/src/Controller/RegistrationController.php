<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Common\Validating\ValidatingForm;
use OPG\Digideps\Frontend\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class RegistrationController extends SymfonyAbstractController
{
    // user clicks on activation link in their email, goes to set password page, and sets password
    // (can re-use existing reset password page?)

    // user is forwarded to page to sign in with their email and new password

    // once signed in, redirect to appropriate /courtorder page

    // user gets an email with a link; this link has no discriminating data
    // (any user can land on this page and register, providing their details match an existing deputy/client)

    #[Route('/register_ng', name: 'register_ng', methods: ['GET', 'POST'])]
    public function registerNgAction(Request $request): Response|array
    {
        $form = $this->createForm(RegistrationType::class)->handleRequest($request);

        $businessLogicError = null;
        $success = false;
        $responseCode = Response::HTTP_OK;

        // handle submitted form, checking data against db tables and associating user with deputy,
        // or showing verification failure message (could be wrong data from user, could be data missing from db if
        // ingest hasn't happened)
        if ($form->isSubmitted()) {
            $validatingForm = new ValidatingForm($form);

            $data = $validatingForm->getStringOrNull('mockDetails');

            if ($form->isValid()) {
                if ($data === 'alreadyregistered') {
                    $businessLogicError = 'User with this email is already registered';
                } elseif ($data === 'nomatch') {
                    $businessLogicError = 'Data could not be verified against our records';
                } elseif ($data === 'verified') {
                    // if verified, create user record, and send user an activation link (to password reset page),
                    // and forward user to confirmation page ("go and check your email");
                    // otherwise, show holding message (either because verification failed, or user's data is unavailable)
                    $success = true;
                } else {
                    $businessLogicError = 'Unable to process submitted registration data';
                    $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                }
            }
        }

        return $this->render('@App/Registration/register_ng.html.twig', [
            'form' => $form->createView(),
            'businessLogicError' => $businessLogicError,
            'success' => $success
        ], new Response(null, $responseCode));
    }
}
