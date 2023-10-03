<?php

namespace App\Form\Report;

use App\Entity\Report\MoneyShortCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyShortCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                 ->add('typeId', FormTypes\HiddenType::class)
                 ->add('present', FormTypes\CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => MoneyShortCategory::class,
             'validation_groups' => ['TODO'],
             'translation_domain' => 'report-money-short',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'category';
    }
}
