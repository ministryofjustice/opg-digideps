<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('fees', FormTypes\CollectionType::class, [
                'type' => new FeeSingleType(),
                'cascade_validation' => true,
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'validation_groups' => ['fees'],
            'cascade_validation' => true,
            'translation_domain' => 'report-pa-fee-expense',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fee';
    }
}
