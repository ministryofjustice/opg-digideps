<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CoDeputyController extends AbstractController
{

    /**
     * @Route("/codeputy/{clientId}/add", name="add_co_deputy")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $user = $this->getUserWithData(['user-clients', 'client']);
        $client = $user->getClients()[0];
        $user = new EntityDir\User();

        $form = $this->createForm(new FormDir\CoDeputyType($client), $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->getRestClient()->post('coDeputy/add', $form->getData());

            $url = $this->getUser()->isOdrEnabled() ?
                $this->generateUrl('odr_index')
                :$this->generateUrl('report_create', ['clientId' => $response['id']]);

            return $this->redirect($url);
        }

        return [
            'client' => $client,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('odr_index')
        ];
    }
}
