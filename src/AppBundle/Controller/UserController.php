<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Service\ApiClient;

/**
* @Route("user")
*/
class UserController extends Controller
{
    /**
     * @Route("/activate/{token}", name="user_activate")
     */
    public function activateAction(Request $request, $token)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */

        // check $token is correct
        $user = $apiClient->getEntity('User', 'find_user_by_token', [ 'query' => [ 'token' => $token ] ]); /* @var $user User*/

        if (!$user->isTokenSentInTheLastHours(48)) {
            throw new \RuntimeException("token expired, require new link");
        }
        
        $form = $this->getSetPasswordForm();
        $form->get('email')->setData($user->getEmail());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                throw new \RuntimeException("Logic to set password not yet implemented");
                // add user
////                developEndpoints();
//                $response = $apiClient->postC('user_set_password', $form->getData());
//                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
//                
//                $request->getSession()->getFlashBag()->add(
//                    'notice', 
//                    'Password has been set. You can now login using the form below.'
//                );
//                
//                return $this->redirect($this->generateUrl('homepage'));
            }
        } 

        
        return $this->render('AppBundle:User:activate.html.twig', [
            'token'=>$token, 
            'form' => $form->createView()
        ]);
    }
    
    
    
    /**
     * @return Form
     */
    private function getSetPasswordForm()
    {
        // validation is in the User class (annotacion format)
        // to put int a class, validate form the builder directly http://symfony.com/doc/current/book/forms.html#adding-validation
        
//        fail;
//        #find a way to validate password twice, and they also have to match();
//        #move form into a class ? look at dennis
//         http://symfony.com/doc/current/book/validation.html
//             http://symfony.com/doc/current/book/forms.html
//             http://symfony.com/doc/current/reference/constraints.html
               
        
        $translator = $this->get('translator');
        
        return $this->createFormBuilder()
            ->add('email', 'text', ['attr'=>['readonly'=>'readonly']])
            ->add('password', 'repeated',[
                'type' => 'password',
                'invalid_message' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate'),
            ])
            ->add('save', 'submit')
            ->getForm();
    }
}