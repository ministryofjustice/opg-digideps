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
        $apiInfo = new \AppBundle\Service\Availability\Api($this->get('apiclient'));
        // add here other services
        
        $response = $this->render('AppBundle:Manage:availability.html.twig', [
            'info' => [
                'API' => $apiInfo->toArray()
            ]
        ]);

        if (!$apiInfo->isHealthy()) {
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

        $apiInfo = $this->getApiInfo();
        $allHealthy = $apiInfo['healthy'];

        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $allHealthy ? 'OK' : 'ERROR: ' . $apiInfo['errors'],
            'time' => microtime(true) - $start
        ]);
        $response->setStatusCode($allHealthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

}