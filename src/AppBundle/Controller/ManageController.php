<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ApiClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/manage")
 */
class ManageController extends Controller
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $data = [
            'api' => $this->getApiHealthData(),
//            'client' => $this->getHealthData(),
        ];
        
        $response = $this->render('AppBundle:Manage:availability.html.twig', [
            'data' => $data
        ]);
        
        if (!$data['api']['healthy']/* || !$data['client']['healthy']*/) {
            $response->setStatusCode('500');
        }
        
        return $response;
    }
    
     /**
     * @Route("/availability/health-check.xml")
     * @Method({"GET"})
     */
    public function healthCheckXmlAction()
    {
        $start = microtime(true);
        
        $api = $this->getApiHealthData();
//        $client = $this->getHealthData();        
        
        $status = $api['healthy']/* && $client['healthy']*/;
        
        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $status ? 'OK' : 'API down',
            'time' => microtime(true) - $start
        ]);
        $response->setStatusCode($status ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');
        
        return $response;
//        
//        
//        $ret = $this->forward('availability');
//        print_r($ret);die;
//        
//        $this->_helper->viewRenderer->setNoRender(true);
//        $this->_helper->getHelper('layout')->disableLayout();
//        header('Content-Type: text/xml');
//        $xml->status = $state['all_ok']?'OK':$serviceDown;
//        $xml->response_time = $response_time;
//        echo $xml->asXML();
    }
    
    
//    private function getHealthData()
//    {
//        $data = [
//            'php_version' => version_compare(PHP_VERSION, "5.4") >= 0,
//            'permissions_app/log' => $this->areLogPermissionCorrect(),
//            'permissions_app/cache' => $this->areCachePermissionCorrect(),
//        ];
//        
//        $data['healthy'] = count(array_filter($data)) === count($data);
//        
//        return $data;
//    }
    
    
    private function getApiHealthData()
    {
        $content = $this->get('apiclient')->get('/manage/availability')->getBody();
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
