<?php

namespace AppBundle\Form\Ndr\Debt;

use AppBundle\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('debts', FormTypes\CollectionType::class, [
                'type' => new DebtSingleType(),
                'cascade_validation' => true,
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Ndr::class,
            'validation_groups'  => ['debts'],
            'cascade_validation' => true,
            'translation_domain' => 'ndr-debts',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'debt';
    }
}
