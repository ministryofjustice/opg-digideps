<?php

namespace AppBundle\Form\Admin;

use AppBundle\Model\FullReviewChecklist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FullReviewChecklistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('decisionExplanation', FormTypes\TextAreaType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FullReviewChecklist::class,
            'translation_domain' => 'admin-checklist',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'full-review-checklist';
    }
}
