<?php

namespace AppBundle\Form\Org;

use AppBundle\Entity\Note as NoteEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NoteType
 *
 *
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
            ->add('id', FormTypes\HiddenType::class)
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => self::getCategories(),
                    'expanded' => false,
                    'required' => false,
                    'placeholder' => 'Please select',
                ]
            )
            ->add('title', FormTypes\TextType::class, ['required' => true])
            ->add('content', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    /**
     * Set default form options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['add_note'],
                'translation_domain' => 'client-notes',
                'data-class' => NoteEntity::class
            ]
        );
    }

    /**
     * Return list of translated categories from the Note entity
     *
     * @return array
     */
    private function getCategories()
    {
        $ret = [];

        foreach (NoteEntity::$categories as $categoryId => $cagtegoryTrqnslationKey) {
            $ret[$categoryId] = $this->translate('form.category.entries.' . $cagtegoryTrqnslationKey);
        }

        return $ret;
    }

    /**
     * Wrapper call to translator
     *
     * @param $key
     * @return string
     */
    private function translate($key)
    {
        return $this->translator->trans($key, [], 'report-note');
    }
}
