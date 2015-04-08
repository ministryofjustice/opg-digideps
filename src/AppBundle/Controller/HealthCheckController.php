<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ApiClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/health-check")
 */
class HealthCheckController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @Template
     */
    public function indexAction()
    {
        $data = [
            'api' => $this->getApiHealthData(),
            'client' => $this->getHealthData(),
        ];
        
        $response = $this->render('AppBundle:HealthCheck:index.html.twig', [
            'data' => $data
        ]);
        
        if (!$data['api']['healthy'] || !$data['client']['healthy']) {
            $response->setStatusCode('500');
        }
        
        return $response;
    }
    
    private function getHealthData()
    {
        $data = [
            'php_version' => version_compare(PHP_VERSION, "5.4") >= 0,
            'permissions_app/log' => $this->areLogPermissionCorrect(),
            'permissions_app/cache' => $this->areCachePermissionCorrect(),
        ];
        
        $data['healthy'] = count(array_filter($data)) === count($data);
        
        return $data;
    }
    
    
    private function getApiHealthData()
    {
        $content = $this->get('apiclient')->get('health-check')->getBody();
        $contentArray = json_decode($content, 1);
        if (json_last_error() !== JSON_ERROR_NONE || empty($contentArray['data'])) {
            throw new \RuntimeException("Cannot decode API response. " . json_last_error_msg());
        }
        return $contentArray['data'];
    }
    
    private function areLogPermissionCorrect()
    {
        return is_writable($this->get('kernel')->getRootDir() . '/logs/');
    }
    
    private function areCachePermissionCorrect()
    {
        return is_writable($this->get('kernel')->getRootDir() . '/cache/');
    }
}
