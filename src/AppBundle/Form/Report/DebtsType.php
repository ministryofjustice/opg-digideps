<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DebtsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('debts', 'collection', [
                'type' => new DebtSingleType(),
                'cascade_validation' => true,
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report\Report',
            'validation_groups' => ['debts'],
            'cascade_validation' => true,
            'translation_domain' => 'report-debts',
        ]);
    }

    public function getName()
    {
        return 'debt';
    }
}
