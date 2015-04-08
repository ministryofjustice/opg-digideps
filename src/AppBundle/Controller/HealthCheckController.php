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
            'api' => $this->getHealthCheckDataFromApi(),
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
        return [
            'healthy' => true,
            'php_version' => true
        ];
    }
    
    
    private function getHealthCheckDataFromApi()
    {
        $content = $this->get('apiclient')->get('health-check')->getBody();
        $contentArray = json_decode($content, 1);
        if (json_last_error() !== JSON_ERROR_NONE || empty($contentArray['data'])) {
            throw new \RuntimeException("Cannot decode API response. " . json_last_error_msg());
        }
        return $contentArray['data'];
    }
}
