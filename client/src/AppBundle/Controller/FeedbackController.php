<?php

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
use Symfony\Component\Routing\Annotation\Route;
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

        if ($form->isSubmitted() && $form->isValid()) {
            // Store in database
            $score = $form->get('satisfactionLevel')->getData();
            if ($score) {
                $this->getRestClient()->post('satisfaction/public', [
                    'score' => $score,
                ]);
            }

            // Send notification email
            $feedbackEmail = $this->getMailFactory()->createGeneralFeedbackEmail($form->getData());
            $this->getMailSender()->send($feedbackEmail);

            $confirmation = $this->get('translator')->trans('collectionPage.confirmation', [], 'feedback');
            $request->getSession()->getFlashBag()->add('notice', $confirmation);
            return $this->redirectToRoute('feedback');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
