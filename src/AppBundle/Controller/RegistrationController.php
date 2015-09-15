<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ApiClient;
use AppBundle\Model as ModelDir;
use AppBundle\Service\MailSender;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
 * @Route("/register")
 */
class RegistrationController extends Controller
{
    
    /**
     * @Route("", name="registration")
     */
    public function registrationAction()
    {
        $selfRegisterData = new ModelDir\SelfRegisterData();
        $form = $this->createForm(new FormDir\SelfRegisterDataType(), $selfRegisterData);
        $request = $this->getRequest();

        if($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if($form->isValid()){

                $data = $form->getData();

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
            }
        }
        return $this->render('AppBundle:Registration:register.html.twig', [ 'form' => $form->createView() ]);
    }
}
