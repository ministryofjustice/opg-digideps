<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends AbstractController
{
    private static $jmsGroups = [
        'notes'
    ];

    /**
     * @Route("/note/add", name="add_note")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $clientId = $request->get('clientId');

        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);

        $report = $client->getCurrentReport();

        $note = new EntityDir\Note($client);
        $template = 'AppBundle:Pa/ClientProfile:addNote.html.twig';

        $form = $this->createForm(
            new FormDir\Pa\NoteType(
                $this->get('translator'),
                $note
            ),
            $note
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $note = $form->getData();

            $this->getRestClient()->post('report/' . $report->getId() . '/note', $note, ['add_note']);
            $request->getSession()->getFlashBag()->add('notice', 'The note has been added');

            return $this->redirectToRoute('report_overview', ['reportId' => $report->getId()]);
        }

        return $this->render($template, [
            'form'  => $form->createView(),
            'client' => $client,
            'report' => $report,
        ]);
    }
}
