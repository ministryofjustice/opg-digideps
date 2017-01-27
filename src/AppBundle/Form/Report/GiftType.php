<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', 'text', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ]);
    }

    public function getName()
    {
        return 'gifts_single';
    }
}
