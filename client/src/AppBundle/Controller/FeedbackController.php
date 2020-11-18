<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Form\FeedbackType;
use AppBundle\Service\Client\Internal\SatisfactionApi;
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

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface  */
    private $form;

    public function __construct(
        SatisfactionApi $satisfactionApi,
        RouterInterface $router,
        TranslatorInterface $translator,
        FormFactoryInterface $form
    ) {
        $this->satisfactionApi = $satisfactionApi;
        $this->router = $router;
        $this->translator = $translator;
        $this->form = $form;
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
                $this->satisfactionApi->createGeneralFeedback($form->getData());
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
