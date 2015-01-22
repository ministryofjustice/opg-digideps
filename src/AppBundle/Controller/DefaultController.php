<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $userArray = [
                      'id' => 1,
                      'firstname' => 'Paul',
                      'lastname' => 'Oforduru',
                      'email' => 'paulo882@yahoo.com',
                      'registration_date' => '2014-11-20 00:00:00',
                      'roles' => 'ROLE_ADMIN',
                      'password' => 'test',
                      'active' => 1,
                      'email_confirmed' => 1,
                      'registration_token' => 'sfsdfdsfdsfds',
                      'token_date' => '2015-01-22 00:00:00',
                      'ga_tracking_id' => 'sfdsfdsfdssf'
                    ];
        $userJson = json_encode($userArray);
        
        $user = $this->get('jms_serializer')->deserialize($userJson,'AppBundle\Entity\User','json');
        $serializedUser = $this->get('jms_serializer')->serialize($user,'json');
        print_r($serializedUser); die;
        return $this->render('default/index.html.twig');
    }
}
