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
                'connection' => $this->isDdConnected(),
                'migrated' => $this->isDdConnected() && $this->isDdMigrated(),
            ],
            'permissions' => [
                'app/log' => $this->areLogPermissionCorrect(),
                'app/cache' => $this->areCachePermissionCorrect()
            ],
            'environment' => [
                'php-version' => $this->isPhpVersionCorrect(),
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