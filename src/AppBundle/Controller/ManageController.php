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
        list($dbHealthy, $dbError) = $this->dbInfo();
        
        $data = [
            'healthy' => $dbHealthy,
            'errors' => $dbError
        ];
        
        return $data;
    }
    
    /**
     * @return array [boolean healthy, error string]
     */
    private function dbInfo()
    {
        try {
            $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findAll();
            return [true, ''];
        } catch (\Exception $e) {
            return [false, 'Database error: ' . $e->getMessage()];
        }
        
    }

}