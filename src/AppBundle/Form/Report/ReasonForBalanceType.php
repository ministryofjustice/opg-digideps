<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReasonForBalanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('balanceMismatchExplanation', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-balance',
            'validation_groups' => ['balance'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'balance';
    }
}
