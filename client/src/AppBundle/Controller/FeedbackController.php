<?php

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
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
        $form = $this->createForm(FeedbackType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Store in database
            $score = $form->get('satisfactionLevel')->getData();
            if ($score) {
                $this->getRestClient()->post('satisfaction/public', [
                    'score' => $score,
                ]);
            }

            // Send notification email
            $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($form->getData());
            $this->getMailSender()->send($feedbackEmail, ['html']);

            return $this->redirectToRoute('feedback_sent');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/feedback/sent", name="feedback_sent")
     * @Template("AppBundle:Feedback:sent.html.twig")
     */
    public function sentAction(Request $request) {}
}
