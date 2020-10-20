<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
use AppBundle\Service\Client\Internal\SatisfactionApi;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends AbstractController
{
    /**
     * @var SatisfactionApi
     */
    private $satisfactionApi;
    /**
     * @var MailSender
     */
    private $mailSender;
    /**
     * @var MailFactory
     */
    private $mailFactory;

    public function __construct(SatisfactionApi $satisfactionApi, MailSender $mailSender, MailFactory $mailFactory)
    {
        $this->satisfactionApi = $satisfactionApi;
        $this->mailSender = $mailSender;
        $this->mailFactory = $mailFactory;
    }

    /**
     * @Route("/feedback", name="feedback")
     * @Template("AppBundle:Feedback:index.html.twig")
     */
    public function create(Request $request)
    {
        $form = $this->createForm(FeedbackType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('satisfactionLevel')->getData()) {
                $this->satisfactionApi->create($form->getData());

                $feedbackEmail = $this->mailFactory->createGeneralFeedbackEmail($form->getData());
                $this->mailSender->send($feedbackEmail);
            }

            $confirmation = $this->get('translator')->trans('collectionPage.confirmation', [], 'feedback');
            $request->getSession()->getFlashBag()->add('notice', $confirmation);
            return $this->redirectToRoute('feedback');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
