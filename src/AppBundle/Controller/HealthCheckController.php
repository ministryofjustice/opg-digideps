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
            'database_connected' => $this->isDdConnected(),
            'database_migrated' => $this->isDdConnected() && $this->isDdMigrated(),
            'permissions_app/log' => $this->areLogPermissionCorrect(),
            'permissions_app/cache' => $this->areCachePermissionCorrect(),
            'php_version' => $this->isPhpVersionCorrect(),
        ];
        $data['healthy'] = count(array_filter($data)) === count($data);

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
        return version_compare(PHP_VERSION, "5.4") >= 0;
    }

}