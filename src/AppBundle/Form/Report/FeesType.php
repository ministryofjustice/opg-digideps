<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('fees', 'collection', [
                'type' => new FeeSingleType(),
                'cascade_validation' => true,
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'validation_groups' => ['fees'],
            'cascade_validation' => true,
            'translation_domain' => 'report-pa-fee-expense',
        ]);
    }

    public function getName()
    {
        return 'fee';
    }
}
