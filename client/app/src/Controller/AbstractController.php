<?php

namespace OPG\Digideps\Frontend\Controller;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Form\Component\AbstractFormType;
use OPG\Digideps\Frontend\Form\Component\AbstractParametrisedFormType;
use OPG\Digideps\Frontend\Form\Component\RenderableForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends SymfonyAbstractController
{
    protected function renderError(string $description, int $statusCode = 500, $message = 'Application error'): Response
    {
        $text = $this->renderView('bundles/TwigBundle/Exception/template.html.twig', [
            'message' => $message,
            'description' => $description,
        ]);

        return new Response($text, $statusCode);
    }

    /**
     * @template P of ?object
     * @template T of AbstractFormType<P>
     * @param class-string<T> $typeClass
     * @param P $parameter
     * @return RenderableForm<T>
     */
    protected function createRenderableForm(string $typeClass, ?string $action = null, ?object $parameter = null, mixed $data = null): RenderableForm
    {
        if (is_a($typeClass, AbstractParametrisedFormType::class, true)) {
            if ($parameter === null) {
                throw new \ValueError("Argument 'parameter' may not be null if using a AbstractParametrisedFormType");
            }
            $typeClass::setParameters($parameter);
        }
        $options = array_filter(['action' => $action]);
        /**
         * @var FormFactoryInterface $factory
         */
        $factory = $this->container->get('form.factory');
        $builder = $factory->createBuilder($typeClass, $data, $options);
        /**
         * @var T $type
         */
        $type = $builder->getType()->getInnerType();
        $form = $builder->getForm();
        return new RenderableForm($type, $form, fn () => $this->createRenderableForm($typeClass, $action));
    }

    protected function getUser(): ?User
    {
        $user = parent::getUser();
        return $user instanceof User ? $user : null;
    }
}
