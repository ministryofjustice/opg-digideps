<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyShortCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoneyShortCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', 'hidden')
                 ->add('present', 'checkbox');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
             'data_class' => MoneyShortCategory::class,
             'validation_groups' => ['TODO'],
             'translation_domain' => 'report-money-short',
        ]);
    }

    public function getName()
    {
        return 'state_benefit';
    }
}
