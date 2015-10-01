<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Exception as AppExceptions;


//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    /**
     * @param queryString skip-mail 
     * 
     * @Route("")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request);

        $user = new \AppBundle\Entity\User();
       
        $this->populateUser($user, $data);
        
        /**
         * Not sure we need this check, email field is set as unique in the db. May be try catch the unique value exception
         * thrown when persist flush ?
         */
        if ($user->getEmail() && $this->getRepository('User')->findOneBy(['email'=>$user->getEmail()])) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.");
        }
        
        // send activation email
        if (empty($request->query->get('skip-mail'))) {
            $activationEmail = $this->getMailFactory()->createActivationEmail($user, 'activate');
            $this->getMailSender()->send($activationEmail, [ 'text', 'html']);
        }
        
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush($user);
        
         //TODO return status code
        
        return ['id'=>$user->getId()];
    }
    
    
     
    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */

        $data = $this->deserializeBodyContent($request);
        
        $this->populateUser($user, $data);
        
        $this->getEntityManager()->flush($user);
        
        return ['id'=>$user->getId()];
    }
    
    
    /**
     * @Route("/{id}/is-password-correct")
     * @Method({"POST"})
     */
    public function isPasswordCorrect(Request $request, $id)
    {
        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */
        
        $data = $this->deserializeBodyContent($request, [
            'password' => 'NotEmpty',
        ]);
        
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        
        $oldPassword = $encoder->encodePassword($data['password'], $user->getSalt());
        if ($oldPassword == $user->getPassword()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * change password, activate user and send remind email
     * @Route("/{id}/set-password")
     * @Method({"PUT"})
     */
    public function changePassword(Request $request, $id)
    {
        $user = $this->findEntityBy('User', $id, 'User not found'); /* @var $user User */
        
        $data = $this->deserializeBodyContent($request, [
            'password_plain' => 'NotEmpty',
        ]);
        
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $newPassword = $encoder->encodePassword($data['password_plain'], $user->getSalt());
        
        $user->setPassword($newPassword);
        
        if (array_key_exists('set_active', $data)) {
           $user->setActive($data['set_active']);
        }
        
        // send change password email
        if (empty($data['send_email'])) {
            // no emails
        } else if ($data['send_email'] == 'activate') {
            $email = $this->getMailFactory()->createChangePasswordEmail($user);
            $this->getMailSender()->send($email,[ 'html']);
            
        } else if ($data['send_email'] == 'password-reset') {
            $email = $this->getMailFactory()->createChangePasswordEmail($user);
            $this->getMailSender()->send($email,[ 'html']);
        }
        
        $this->getEntityManager()->flush();
        
        return $user;
    }
    
    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($id)
    {
        return $this->findEntityBy('User', $id, 'User not found');
    }
    
    /**
     * @Route("/{adminId}/{id}")
     * @Method({"DELETE"})
     * 
     * @param integer $id
     * @return array []
     * @throws \RuntimeException
     */
    public function delete($id,$adminId)
    {
        $adminUser = $this->getRepository('User')->find($adminId);
        
        if(empty($adminUser) || ($adminUser->getRole()->getRole() != "ROLE_ADMIN") || ($adminId == $id)){
            throw new AppExceptions\Auth("You are not authorized to perform this action");
        }
        
        $user = $this->getRepository('User')->find($id);
        
        if(empty($user)){
            throw new AppExceptions\NotFound("User not found");
        }
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
        
        return [];
    }

    
    /**
     * @Route("/get-all/{order_by}/{sort_order}", defaults={"order_by" = "firstname", "sort_order" = "ASC"})
     * @Method({"GET"})
     */
    public function getAll($order_by, $sort_order)
    {
        return $this->getRepository('User')->findBy([],[ $order_by => $sort_order ]);
    }

    
    /**
     * Requires client secret 
     * 
     * @Route("/recreate-token/{email}/{type}", defaults={"email": "none"}, requirements={
     *   "type" = "(activate|pass-reset)"
     * })
     * @Method({"PUT"})
     */
    public function recreateToken(Request $request, $email, $type)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }
        $user = $this->findEntityBy('User', ['email'=>$email]);
         if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRole()->getRole() . ' user role not allowed from this client.', 403);
        }
        
        $user->recreateRegistrationToken();
        
        $this->getEntityManager()->flush($user);

        switch ($type) {
            case 'activate':
                // send acivation email to user
                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, [ 'text', 'html']);
                break;

            case 'pass-reset':
                // send reset password email
                $resetPasswordEmail = $this->getMailFactory()->createResetPasswordEmail($user);
                $this->getMailSender()->send($resetPasswordEmail, [ 'text', 'html']);
                break;
        }
        
        return $user->getId();
    }
    
    
    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }
        $user = $this->findEntityBy('User', ['registrationToken'=>$token], "User not found"); /* @var $user User */
         if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRole()->getRole() . ' user role not allowed from this client.', 403);
        }
        
        return $user;
    }
    
    
    /**
     * call setters on User when $data contains values
     * 
     * @param User $user
     * @param array $data
     */
    private function populateUser(User $user, array $data)
    {
        // Cannot easily(*) use JSM deserialising with already constructed objects. 
        // Also. It'd be possible to differentiate when a NULL value is intentional or not
        // (*) see options here https://github.com/schmittjoh/serializer/issues/79
        // http://jmsyst.com/libs/serializer/master/event_system
        
        $this->hydrateEntityWithArrayData($user, $data, [
            'firstname' => 'setFirstname', 
            'lastname' => 'setLastname', 
            'email' => 'setEmail', 
            'address1' => 'setAddress1', 
            'address2' => 'setAddress2', 
            'address3' => 'setAddress3', 
            'address_postcode' => 'setAddressPostcode', 
            'address_country' => 'setAddressCountry', 
            'phone_alternative' => 'setPhoneAlternative', 
            'phone_main' => 'setPhoneMain', 
        ]);
        
        if (array_key_exists('role_id', $data)) {
            $role = $this->findEntityBy('Role', $data['role_id'], 'Role not found');
            $user->setRole($role);
        }
        
        if (array_key_exists('last_logged_in', $data)) {
            $user->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }
        
        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }
        
        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }
       
    }
    
}
