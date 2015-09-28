<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ApiClient;
use AppBundle\Model as ModelDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/register")
 */
class RegistrationController extends Controller
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

        if($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if($form->isValid()){

                $data = $form->getData();

                try {
                
                    $this->get('apiclient')->postC('selfregister' , $data);
                    
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
                    if ($e->getCode() == 422)
                    {
                        $form->get('email')->addError(new FormError("That email has already been registered with this service."));
                    }
                }
            }
        }
        return $this->render('AppBundle:Registration:register.html.twig', [ 'form' => $form->createView() ]);
    }
}
