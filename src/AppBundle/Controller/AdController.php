<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\DataImporter\CsvToArray;
use Symfony\Component\Form\FormError;
use AppBundle\Exception\RestClientException;

/**
* @Route("/ad")
*/
class AdController extends AbstractController
{
    /**
     * @Route("/", name="ad_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [
        ];
    }
    
    /**
     * @Route("/register", name="ad_register")
     * @Template
     */
    public function registerAction(Request $request)
    {
        return [
        ];
    }
    
    /**
     * @Route("/login", name="ad_login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        return [
        ];
    }
}
