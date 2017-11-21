<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Odr::class,
            'validation_groups'  => ['debts'],
            'cascade_validation' => true,
            'translation_domain' => 'odr-debts',
        ]);
    }

    public function getName()
    {
        return 'debt';
    }
}
