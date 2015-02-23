<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/create", name="report_create")
     * @Template()
     */
    public function createAction()
    {
        return [];
    }
}