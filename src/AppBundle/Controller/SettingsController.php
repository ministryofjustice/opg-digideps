<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SettingsController extends AbstractController
{
    /**
     * @Route("/settings", name="account_settings")
     * @Template()
     **/
    public function indexAction()
    {
        $user = $this->getUserWithData(['client', 'report']);
        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        return [
            'client' => $client,
        ];
    }


    /**
     * @Route("/pa/settings", name="pa_settings")
     * @Template("AppBundle:Pa:settings.html.twig")
     */
    public function paSettingsAction()
    {
        return [];
    }
}
