<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
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

        /** @var Request $request */
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            
            try {

                $this->get('restClient')->registerUser($data);

                $bodyText = $this->get('translator')->trans('thankyou.body', [], 'register');
                $email = $data->getEmail();
                $bodyText = str_replace("{{ email }}", $email, $bodyText);


                $signInText = $this->get('translator')->trans('signin', [], 'register');
                $signIn = '<a href="' . $this->generateUrl("login") . '">' . $signInText . '</a>';
                $bodyText = str_replace("{{ sign_in }}", $signIn, $bodyText);

                return $this->render('AppBundle:Registration:thankyou.html.twig',[
                    'bodyText' => $bodyText
                ]);

            } catch(\Exception $e) {
                if ($e->getCode() == 422) {
                    //move this logic to a separate form field contraint (see existing password validator as example)
                    $form->get('email')->addError(new FormError("That email has already been registered with this service."));
                }
                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }
            
        return $this->render('AppBundle:Registration:register.html.twig', [ 'form' => $form->createView() ]);
    }
}
