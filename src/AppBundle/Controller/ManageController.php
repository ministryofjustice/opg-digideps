<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/manage")
 */
class ManageController extends Controller
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     */
    public function availabilityAction()
    {
        $data = [
            'database_connected' => $this->isDdConnected(),
            //'permissions_app/log' => $this->areLogPermissionCorrect(),
            //'permissions_app/cache' => $this->areCachePermissionCorrect(),
            //'php_version' => $this->isPhpVersionCorrect(),
        ];
        $data['healthy'] = count(array_filter($data)) === count($data);

        return $data;
    }
    
    
    private function isDdConnected()
    {
        try {
            $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findAll();
            return true;
        } catch (\Exception $e) {
            return false;
        }
        
    }
    
//    private function areLogPermissionCorrect()
//    {
//        return is_writable($this->get('kernel')->getRootDir() . '/logs/');
//    }
//    
//    private function areCachePermissionCorrect()
//    {
//        return is_writable($this->get('kernel')->getRootDir() . '/cache/');
//    }
//    
//    private function isPhpVersionCorrect()
//    {
//        return version_compare(PHP_VERSION, "5.4") >= 0;
//    }

}