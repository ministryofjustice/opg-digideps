<?php

namespace AppBundle\Form\Org;

use AppBundle\Entity\Note as NoteEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NoteType
 *
 *
 */
class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add(
                'category',
                ChoiceType::class,
                [
                    'choices' => self::getCategoriesChoices(),
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
    private function getCategoriesChoices()
    {
        $ret = [];

        foreach (NoteEntity::$categories as $categoryId => $cagtegoryTrqnslationKey) {
            $ret['form.category.entries.' . $cagtegoryTrqnslationKey] = $categoryId;
        }

        return $ret;
    }
}
