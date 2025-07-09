<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\FeedbackType;
use App\Service\Client\Internal\SatisfactionApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly SatisfactionApi $satisfactionApi,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $form,
    ) {
    }

    /**
     * @Route("/feedback", name="feedback")
     *
     * @Template("@App/Feedback/index.html.twig")
     */
    public function create(Request $request): array|RedirectResponse
    {
        $form = $this->form->create(FeedbackType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [FeedbackType::HONEYPOT_FIELD_NAME => $honeyPot] = $form->getData();
            if (empty($honeyPot)) {
                // Not spam
                if ($form->get('satisfactionLevel')->getData()) {
                    $this->satisfactionApi->createGeneralFeedback($form->getData());
                }

                $confirmation = $this->translator->trans('collectionPage.confirmation', [], 'feedback');
                $request->getSession()->getFlashBag()->add('notice', $confirmation);
            } else {
                // Spam detected
                $error = $this->translator->trans('collectionPage.spamError', [], 'feedback');
                $request->getSession()->getFlashBag()->add('error', $error);
            }

            return new RedirectResponse($this->router->generate('feedback'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
