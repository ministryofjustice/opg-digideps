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
        $translator = $this->get('translator');
        
        /** @var Request $request */
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            
            try {

                $this->get('restClient')->registerUser($data);

                $bodyText = $translator->trans('thankyou.body', [], 'register');
                $email = $data->getEmail();
                $bodyText = str_replace("{{ email }}", $email, $bodyText);


                $signInText = $translator->trans('signin', [], 'register');
                $signIn = '<a href="' . $this->generateUrl("login") . '">' . $signInText . '</a>';
                $bodyText = str_replace("{{ sign_in }}", $signIn, $bodyText);

                return $this->render('AppBundle:Registration:thankyou.html.twig',[
                    'bodyText' => $bodyText
                ]);

            } catch(\Exception $e) {
                
                switch ((int)$e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($translator->trans('email.existing', [], 'register')));
                        break;
                    
                    case 421:
                        $form->addError(new FormError($translator->trans('matchingError', [], 'register')));
                        break;
                    
                    default:
                        $form->addError(new FormError($translator->trans('genericError', [], 'register')));
                }
                
                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }
            
        return $this->render('AppBundle:Registration:register.html.twig', [ 'form' => $form->createView() ]);
    }
}
