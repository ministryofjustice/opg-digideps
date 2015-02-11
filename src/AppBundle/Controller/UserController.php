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
use AppBundle\Form\SetPasswordType;

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
        $translator = $this->get('translator');
        
        // check $token is correct
        $user = $apiClient->getEntity('User', 'find_user_by_token', [ 'query' => [ 'token' => $token ] ]); /* @var $user User*/

        if (!$user->isTokenSentInTheLastHours(48)) {
            throw new \RuntimeException("token expired, require new link");
        }
        
        $formType = new SetPasswordType([
            'passwordMismatchMessage' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate')
        ]);
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                throw new \RuntimeException("Logic to set password not yet implemented");
                $user;
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
}