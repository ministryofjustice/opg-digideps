<?php

namespace AppBundle\Form\Ndr\Debt;

use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('debtManagement', FormTypes\TextareaType::class)
            ->add('save', FormTypes\SubmitType::class);
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
