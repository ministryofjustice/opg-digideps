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
 * @Route("/admin/client/{id}")
 */
class ClientController extends AbstractController
{

    /**
     * @Route("/detail", name="client_detail")
     * @Template()
     */
    public function detailAction(Request $request, $id)
    {


        $client = $this->getRestClient()->get('client/' . $id, 'Client[]');

        return [
            'client' => $client
        ];
    }

}
