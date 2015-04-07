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
    private function checkIsBehatBrowser()
    {
        $expectedSecretParam = md5('behat-dd-' . $this->container->getParameter('secret'));
        
        $isBehat = $_SERVER['REMOTE_ADDR'] === '127.0.0.1' 
                   && $_SERVER['HTTP_USER_AGENT'] === 'Symfony2 BrowserKit';
        $isProd = $this->get('kernel')->getEnvironment() == 'prod';
        $isSecretParamCorrect = $this->getRequest()->get('secret') == $expectedSecretParam;
        
        if (!$isBehat || $isProd /*|| !$isSecretParamCorrect*/) {
            throw $this->createNotFoundException('Not found');
        }
    }
    
    /**
     * @Route("/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('apiclient')->get('behat/email')->getBody();
        
        return new Response(json_decode($content, 1)['data']);
    }
    
    /**
     * @Route("/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('apiclient')->delete('behat/email')->getBody();
        
        return new Response($content);
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-cot/{cot}")
     * @Method({"GET"})
     */
    public function changeReportCot($reportId, $cot)
    {
        $this->checkIsBehatBrowser();
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'cotId' => $cot
        ]));
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->checkIsBehatBrowser();
        
        $this->get('apiclient')->delete('behat/users/behat-users');
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->get('apiclient')->putC('report/' . $reportId, json_encode([
            'endDate' => $dateYmd
        ]));
        
        return new Response('done');
    }
}
