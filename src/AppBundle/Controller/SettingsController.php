<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SettingsController extends AbstractController
{
    /**
     * @Route("/deputyship-details", name="account_settings")
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
}
