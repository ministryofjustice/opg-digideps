<?php

namespace AppBundle\Controller;

use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/register")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("", name="register")
     */
    public function registrationAction()
    {
        $selfRegisterData = new ModelDir\SelfRegisterData();
        $form = $this->createForm(new FormDir\SelfRegisterDataType(), $selfRegisterData);
        $translator = $this->get('translator');
        $vars = [];

        /** @var Request $request */
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $user = $this->getRestClient()->registerUser($data);
                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail);

                $bodyText = $translator->trans('thankyou.body', [], 'register');
                $email = $data->getEmail();
                $bodyText = str_replace('{{ email }}', $email, $bodyText);

                $signInText = $translator->trans('signin', [], 'register');
                $signIn = '<a href="'.$this->generateUrl('login').'">'.$signInText.'</a>';
                $bodyText = str_replace('{{ sign_in }}', $signIn, $bodyText);

                return $this->render('AppBundle:Registration:thankyou.html.twig', [
                    'bodyText' => $bodyText,
                ]);
            } catch (\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($translator->trans('email.existingError', [], 'register')));
                        break;

                    case 421:
                        $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                        break;

                    case 424:
                        $form->get('postcode')->addError(new FormError($translator->trans('postcode.matchingError', [], 'register')));
                        break;

                     case 425:
                        $form->addError(new FormError($translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                }

                $this->get('logger')->error(__METHOD__.': '.$e->getMessage().', code: '.$e->getCode());
            }
        }

        // send different URL to google analytics
        if (count($form->getErrors())) {
            $vars['gaCustomUrl'] = '/register/form-errors';
        }

        return $this->render('AppBundle:Registration:register.html.twig', $vars + [
            'form' => $form->createView(),
        ]);
    }
}
