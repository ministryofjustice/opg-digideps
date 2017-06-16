<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity\Note as NoteEntity;
use Common\Form\Elements\InputFilters\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NoteType
 *
 * @package AppBundle\Form\Pa
 */
class NoteType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => self::getCategories(),
                    'expanded' => false,
                    'required' => false,
                    'empty_value' => 'Please select',
                ]
            )
            ->add('title', 'text', ['required' => true])
            ->add('content', 'textarea')
            ->add('save', 'submit');
    }

    /**
     * Set default form options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['add_note'],
                'translation_domain' => 'client-notes',
                'data-class' => NoteEntity::class
            ]
        );
    }

}
