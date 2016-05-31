<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DebtsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('hasDebts', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'required' => true
            ))
            ->add('debts', 'collection', [
                'type' => new DebtSingleType(),
                'error_bubbling' => false,
                'cascade_validation' => true
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report',
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
