<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ApiClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/behat")
 */
class BehatController extends Controller
{
    /**
     * @Route("/email-get-last")
     * @Method({"GET"})
     */
    public function getLastAction()
    {
        $content = $this->get('apiclient')->get('behat/email')->getBody();
        
        return new Response(json_decode($content, 1)['data']);
    }
    
    /**
     * @Route("/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $content = $this->get('apiclient')->delete('behat/email')->getBody();
        
        return new Response($content);
    }
    
    /**
     * @Route("/report/{reportId}/change-report-cot/{cot}")
     * @Method({"GET"})
     */
    public function changeReportCot($reportId, $cot)
    {
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'cotId' => $cot
        ]));
        
        return new Response('done');
    }
}
