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
            'healthy' => true,
            'database_connected' => $this->isDdConnected(),
            'database_migrated' => $this->isDdConnected() && $this->isDdMigrated(),
            'permissions_app/log' => $this->areLogPermissionCorrect(),
            'permissions_app/cache' => $this->areCachePermissionCorrect(),
            'php-version' => $this->isPhpVersionCorrect(),
        ];
        
        // set healthy to false if one is false
        foreach ($data as $v) {
            if (!$v) {
                 $data['app']['healthy'] = false;
            }
        }

        return $data;
    }
    
    private function isDdConnected()
    {
        return true;
    }
    
    private function isDdMigrated()
    {
        return true;
    }
    
    private function areLogPermissionCorrect()
    {
        return true;
    }
    
    private function areCachePermissionCorrect()
    {
        return true;
    }
    
    private function isPhpVersionCorrect()
    {
        return true;
    }

}