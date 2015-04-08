<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/health-check")
 */
class HealthCheckController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $data = [
            'app' => [
                'healthy' => true,
            ],
            'database' => [
                'connection' => true,
                'migrated' => true,
            ],
            'permissions' => [
                'app/log' => true,
                'app/cache' => true
            ],
            'environment' => [
                'php-version' => true,
            ],
        ];
        
        // set healthy to false if one is false
        foreach ($data as $v) {
            foreach ($v as $v2) {
                if (!$v2) {
                     $data['app']['healthy'] = false;
                }
            }
        }

        return $data;
    }

}