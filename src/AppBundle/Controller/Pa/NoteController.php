<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
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
                $this->getRestClient()->post('report/' . $report->getId() . '/note', $note, ['note'], 'Note');

                $request->getSession()->getFlashBag()->add('info', 'The note has been added');

                return $this->redirectToRoute('report_overview', ['reportId'=>$report->getId()]);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }
}
