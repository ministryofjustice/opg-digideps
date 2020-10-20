<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
use AppBundle\Service\Client\Internal\SatisfactionApi;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    /** @var SatisfactionApi */
    private $satisfactionApi;

    /** @var MailSender */
    private $mailSender;

    /** @var MailFactory */
    private $mailFactory;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface  */
    private $form;

    public function __construct(
        SatisfactionApi $satisfactionApi,
        MailFactory $mailFactory,
        MailSender $mailSender,
        RouterInterface $router,
        TranslatorInterface $translator,
        FormFactoryInterface $form
    ) {
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
        $this->router = $router;
        $this->translator = $translator;
        $this->form = $form;
        $this->satisfactionApi = $satisfactionApi;
    }

    /**
     * @Route("/feedback", name="feedback")
     * @Template("AppBundle:Feedback:index.html.twig")
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function create(Request $request)
    {
        $form = $this->form->create(FeedbackType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('satisfactionLevel')->getData()) {
                $this->satisfactionApi->create($form->getData());

                $feedbackEmail = $this->mailFactory->createGeneralFeedbackEmail($form->getData());
                $this->mailSender->send($feedbackEmail);
            }

            $confirmation = $this->translator->trans('collectionPage.confirmation', [], 'feedback');
            $request->getSession()->getFlashBag()->add('notice', $confirmation);

            return new RedirectResponse($this->router->generate('feedback'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
