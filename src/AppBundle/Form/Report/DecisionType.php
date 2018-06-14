<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Decision;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder/*->add('title', 'text')*/
        ->add('description', 'textarea')
            ->add('clientInvolvedBoolean', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
            ])
            ->add('clientInvolvedDetails', 'textarea')
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-decisions',
            'validation_groups' => ['decision-description', 'decision-client-involved', 'decision-client-involved-details'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'decision';
    }
}
