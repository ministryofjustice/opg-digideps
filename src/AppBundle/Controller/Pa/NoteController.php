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
     * @Route("/report/{reportId}/note", name="add_note")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $template = 'AppBundle:Pa/ClientProfile:addNote.html.twig';

        $form = $this->createForm(
            new FormDir\Pa\NoteType(
                $this->get('translator'),
                new EntityDir\Note($report)
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $note = $form->getData();

            try {
                $this->getRestClient()->post('report/' . $report->getId() . '/note', $note, ['add_note'], 'Note');

                $request->getSession()->getFlashBag()->add('info', 'The note has been added');

                return $this->redirectToRoute('report_overview', ['reportId'=>$report->getId()]);
            } catch (RestClientException $e) {
                $form->get('title')->addError(new FormError($e->getData()['message']));

            }
        }

        return $this->render($template, [
            'form'  => $form->createView(),
            'report' => $report,
        ]);
    }
}
