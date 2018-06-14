<?php

namespace AppBundle\Form\Ndr\Debt;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('debtManagement', 'textarea')
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['ndr-debt-management'],
            'translation_domain' => 'ndr-debts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'debtManagement';
    }
}
