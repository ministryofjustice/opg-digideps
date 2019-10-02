<?php

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends AbstractController
{
    /**
     * @Route("/feedback", name="feedback")
     * @Template("AppBundle:Feedback:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(FeedbackReportType::class, null, [
            'include_contact_information' => true
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Store in database
            $this->getRestClient()->post('satisfaction', [
                'score' => $form->get('satisfactionLevel')->getData(),
            ]);

            // Send notification email
            $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($form->getData());
            $this->getMailSender()->send($feedbackEmail, ['html']);

            return $this->render('AppBundle:Feedback:sent.html.twig');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
