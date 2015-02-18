<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @Route("client")
*/
class UserController extends Controller
{
    
    /**
     * @Route("/details", name="client_details")
     */
    public function detailsAction(Request $request)
    {
        return new Response('client details page. TODO');
    }
}