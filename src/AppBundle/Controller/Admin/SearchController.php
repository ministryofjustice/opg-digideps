<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="admin_search")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit'       => 100,
            'offset'      => $request->get('offset', 'id'),
            'q'           => '',
            'order_by'    => 'id',
            'sort_order'  => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchClientType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $filters = $form->getData() + $filters;
        }

        $clients = $this->getRestClient()->get('client/get-all?' . http_build_query($filters), 'Client[]');

        return [
            'form'    => $form->createView(),
            'clients'   => $clients,
            'filters' => $filters,
        ];
    }


}
