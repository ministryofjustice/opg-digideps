<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report\Report',
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
